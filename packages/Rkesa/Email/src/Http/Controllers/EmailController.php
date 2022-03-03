<?php

namespace Rkesa\Email\Http\Controllers;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Calendar\Http\Helpers\ChecklistPDFCreator;
use Rkesa\Calendar\Models\Checklist;
use Rkesa\Calendar\Models\Event;
use Rkesa\Estimate\Models\ResourceAttachment;
use App\User;
use Log;
use Exception;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Service\Models\Service;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Estimate\Http\Helpers\EstimatePDFCreator;
use Rkesa\Service\Models\ServiceStateAction;

include_once base_path('packages/Rkesa/Email/vendor/afterlogic_webmail/libraries/afterlogic/api.php');

class EmailController extends Controller
{
    public function login(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();

            if (class_exists('CApi') && \CApi::IsValid()) {
                // Getting required API class
                $oApiIntegratorManager = \CApi::Manager('integrator');

                if ($user->email_password === null){
                    throw new Exception('Email password is empty');
                }

                // attempting to obtain object for account we're trying to log into
                $oAccount = $oApiIntegratorManager->LoginToAccount($user->email, $user->email_password);
                if ($oAccount) {
                    // populating session data from the account
                    $oApiIntegratorManager->SetAccountAsLoggedIn($oAccount);
                } else {
                    // login error
                    $res->errcode = 1;
                    $res->errmess = $oApiIntegratorManager->GetLastErrorMessage();
                }
            }else{
                $res->errcode = 1;
                $res->errmess = 'WebMail API isn\'t available';
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function action_email(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            if (class_exists('CApi') && \CApi::IsValid()) {
                $sEmail = Auth::user()->email;
                $sPassword = Auth::user()->email_password;

                $oApiIntegratorManager = \CApi::Manager('integrator');
                $oAccount = $oApiIntegratorManager->LoginToAccount($sEmail, $sPassword);

                if ($oAccount) {
                    $oApiIntegratorManager->SetAccountAsLoggedIn($oAccount);
                }

                $service_state_action = ServiceStateAction::find($request['action']);
                $global_data = $request->input('global_data', null);
                $event_start_formatted = (new DateTime($global_data['event_start']))->format('Y-m-d H:i');

                $service = Service::find($request['service']);
                $master_estimate_number = '';
                $first_percent = '';
                if ($service->master_estimate_id) {
                    $estimate = Estimate::find($service->master_estimate_id);
                    $master_estimate_number = $estimate->get_estimate_number();
                    $first_percent = $estimate->estimate_pay_stages->first()['percent'];
                }
                if ($global_data['event_id'] != '') {
                    $event = Event::find($global_data['event_id']);
                }

                //estimate numbers
                $estimate_numbers = [];
                if ($request['estimates'] !== null){
                    foreach ($request['estimates'] as $estimate_id) {
                        $tmp_estimate = Estimate::find($estimate_id);
                        array_push($estimate_numbers, $tmp_estimate->get_estimate_number());
                    }
                }
                $joined_numbers = join(', ', $estimate_numbers);

                //phones
                $phones = [];
                foreach ($service->client_contact->client_contact_phones as $phone) {
                    array_push($phones, $phone->phone_number);
                }
                $phones = join(', ', $phones);

                $service_url = '';
                if ($service->client_contact_id) {
                    $service_url = url('/contacts/' . $service->client_contact_id . '?service_id=' . $service->id);
                }

                $replace_from = array('{sent_estimate_numbers}', '{task_description}', '{uploaded_link}', '{event_start}',
                    '{master_estimate_number}', '{first_pay_step_percent}', '{service_number}', '{service_address}', '{selected_estimate_numbers}',
                    '{client_fullname}', '{client_email}', '{client_phones}', '{service_priority}', '{service_url}');
                $replace_to = array($global_data['sent_estimate_numbers'], $global_data['task_description'], $global_data['uploaded_link'], $event_start_formatted,
                    $master_estimate_number, $first_percent, $service->get_service_number(), $service->address, $joined_numbers,
                    self::full_name($service->client_contact), $service->client_contact->client_contact_emails->first()->email, $phones,
                    $service->service_priority ? $service->service_priority->name : '', $service_url);

                $tmp_text = "<!DOCTYPE html><html><body>" . $service_state_action->email_text . "<br>";

                $sSubject = str_replace($replace_from, $replace_to, $service_state_action->email_subject);

                if ($service_state_action->email_type == 1) {         // Client
                    $sTo = $service->client_contact->client_contact_emails->first()->email;
                } elseif ($service_state_action->email_type == 2) {    // User
                    $sTo = $service->responsible_user->email;
                } elseif ($service_state_action->email_type == 3) {    // Fix address
                    $sTo = $service_state_action->email_address;
                } elseif ($event) {                                    // Task responsible
                    $sTo = $event->user['email'];
                } else {
                    $sTo = '';
                }
                
                $sCc = $service_state_action->email_cc ? Auth::user()->email : '';
                $sBcc = '';
                $bReadingConfirmation = false;

                $oApiMailManager = \CApi::Manager('mail');
                $oMessage = \MailSo\Mime\Message::NewInstance();
                $oMessage->RegenerateMessageId();
                $oFrom = \MailSo\Mime\Email::NewInstance($oAccount->Email, $oAccount->FriendlyName);
                $oMessage->SetFrom($oFrom)->SetSubject($sSubject);

                $oToEmails = \MailSo\Mime\EmailCollection::NewInstance($sTo);
                if ($oToEmails && $oToEmails->Count()) {
                    $oMessage->SetTo($oToEmails);
                }
                $oCcEmails = \MailSo\Mime\EmailCollection::NewInstance($sCc);
                if ($oCcEmails && $oCcEmails->Count()) {
                    $oMessage->SetCc($oCcEmails);
                }

                $oBccEmails = \MailSo\Mime\EmailCollection::NewInstance($sBcc);
                if ($oBccEmails && $oBccEmails->Count()) {
                    $oMessage->SetBcc($oBccEmails);
                }

                if ($bReadingConfirmation) {
                    $oMessage->SetReadConfirmation($oAccount->Email);
                }

                // Attachments - begin
                $fichas_list = [];
                $res->sent_estimate_numbers = '';
                // Master estimate
                if ($service->master_estimate_id && $service_state_action->email_include_estimate_type == 1) {
                    $creator = new EstimatePDFCreator;
                    $result = $creator->render_pdf($estimate);
                    $rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString($result);
                    $oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, $estimate->get_estimate_number() . '.pdf'));
                    if ($service_state_action->email_include_resource_attachments) {
                        $fichas_list = self::get_resource_ids($estimate);
                    }
                    $res->sent_estimate_numbers = $master_estimate_number;
                }

                // Selected estimates
                if ($service_state_action->email_include_estimate_type == 2 && $request['estimates']) {
                    $creator = new EstimatePDFCreator;
                    foreach ($request['estimates'] as $estimate_id) {
                        $in_estimate = Estimate::find($estimate_id);
                        $result = $creator->render_pdf($in_estimate);
                        $rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString($result);
                        $oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, $in_estimate->get_estimate_number() . '.pdf'));
                        if ($service_state_action->email_include_resource_attachments) {
                            $fichas_list = array_merge($fichas_list, self::get_resource_ids($in_estimate));
                        }
                    }
                    $fichas_list = array_unique($fichas_list);
                    $res->sent_estimate_numbers = $joined_numbers;
                }

                // Fichas list
                foreach ($fichas_list as $ficha_id) {
                    $res_ats = ResourceAttachment::where('resource_id', $ficha_id)->get();
                    foreach ($res_ats as $res_at) {
                        //$rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString(file_get_contents(public_path() . $res_at->url));
                        //$oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, $res_at->name . self::extension($res_at->url)));
                        $tmp_text = $tmp_text . '<a href="'.env('APP_URL').''.$res_at->url.'">'.$res_at->name.self::extension($res_at->url).'</a> <br>';
                    }
                }

                // Fixed file
                if ($service_state_action->email_file) {
                    $rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString(file_get_contents(public_path() . $service_state_action->email_file));
                    $oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, $service_state_action->email_filename . self::extension($service_state_action->email_file)));
                    //$tmp_text = $tmp_text . '<a href="'.env('APP_URL').''.$service_state_action->email_file.'">'.$service_state_action->email_filename.self::extension($service_state_action->email_file).'</a> <br>';
                }

                $tmp_text = $tmp_text . "</body></html>";
                $sText = str_replace($replace_from, $replace_to, $tmp_text) . '<br><br>' . $oAccount->Signature;
                $sTextConverted = \MailSo\Base\HtmlUtils::ConvertHtmlToPlain($sText);
                $oMessage->AddText($sTextConverted, false);

                $mFoundDataURL = array();
                $aFoundedContentLocationUrls = array();
                $aFoundCids = array();

                $htmlTextConverted = \MailSo\Base\HtmlUtils::BuildHtml($sText, $aFoundCids, $mFoundDataURL, $aFoundedContentLocationUrls);
                $oMessage->AddText($htmlTextConverted, true);

                // Checklist
                if ($service_state_action->email_include_checklist_id != 0 && $event) {
                    $checklist = Checklist::find($service_state_action->email_include_checklist_id);
                    $creator = new ChecklistPDFCreator;
                    $result = $creator->render_pdf($event, $service, $checklist);
                    $rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString($result);
                    $oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, 'Checklist.pdf'));
                }
                // Attachments - end

                if ($service_state_action->email_show) {
                    $sDraftFolder = "Drafts";
                    $oFolders = $oApiMailManager->getFolders($oAccount);
                    $aFolders = array();
                    $oFolders->foreachWithSubFolders(function ($oFolder) use (&$aFolders, &$sDraftFolder) {
                        if ($oFolder->getType() === \EFolderType::Drafts) {
                            $sDraftFolder = $oFolder->getFullName();
                        }
                    });
                    $sDraftFolder = mb_convert_encoding($sDraftFolder, "UTF7-IMAP", "UTF-8");
                    $res->draft = $oApiMailManager->saveMessage($oAccount, $oMessage, $sDraftFolder); // takes A LOT of time
                } else {
                    $sSentFolder = "Sent Items";
                    $oFolders = $oApiMailManager->getFolders($oAccount);
                    $aFolders = array();
                    $oFolders->foreachWithSubFolders(function ($oFolder) use (&$aFolders, &$sSentFolder) {
                        if ($oFolder->getType() === \EFolderType::Sent) {
                            $sSentFolder = $oFolder->getFullName();
                        }
                    });
                    $sSentFolder = mb_convert_encoding($sSentFolder, "UTF7-IMAP", "UTF-8");
                    $res->info = $oApiMailManager->sendMessage($oAccount, $oMessage, null, $sSentFolder);
                }
            } else {
                $res->errcode = 1;
                $res->errmess = "Email API error";
            }
        // Experimental functionality
//        } catch (\CApiManagerException $oException) {
//
//            $mes = $oException->getMessage();
//            switch ($oException->getCode())
//            {
//                case \Errs::Mail_InvalidRecipients:
//                    $mes = 'Invalid recipients';
//                    break;
//            }
//
//            throw new Exception($mes, $oException->getCode());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    private function full_name($card) {
        return ($card['name'] ? $card['name'] : '') . ' ' . ($card['surname'] ? $card['surname'] : '');
    }

    private function extension($str) {
        return substr($str, strrpos($str, '.'));
    }

    private function get_resource_ids($estimate){
        $fichas_list = [];
        foreach ($estimate->lines_with_join() as $line) {
            if ($line['lineable_type'] == '\App\EstimateLineFicha') {
                $resources = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->get();
                foreach ($resources as $resource) {
                    if (!in_array($resource->resource_id, $fichas_list)) {
                        array_push($fichas_list, $resource->resource_id);
                    }
                }
            }
        }
        return $fichas_list;
    }

}
