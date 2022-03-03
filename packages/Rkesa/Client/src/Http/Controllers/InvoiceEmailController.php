<?php

namespace Rkesa\Client\Http\Controllers;

use Log;
use App\User;
use DateTime;
use Exception;
use App\Invoice;
use Carbon\Carbon;
use App\EmailTemplate;
use App\GlobalSettings;
use App\CompanyInformation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Calendar\Models\Event;
use Rkesa\Service\Models\Service;
use Illuminate\Support\Facades\DB;
use Rkesa\Estimate\Models\Estimate;
use App\Events\SitesSettingsChanged;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Rkesa\Calendar\Models\Checklist;
use Illuminate\Support\Facades\Input;
use Intervention\Image\Facades\Image;
use Rkesa\Service\Models\ServiceStateAction;
use Rkesa\Estimate\Models\ResourceAttachment;
use Rkesa\Client\Http\Helpers\FaturaPDFCreator;
use Rkesa\Client\Http\Helpers\OpenSslHelper;
use Rkesa\Estimate\Http\Helpers\EstimatePDFCreator;
use Rkesa\Estimate\Http\Helpers\PlanningPDFCreator;
use Rkesa\Calendar\Http\Helpers\ChecklistPDFCreator;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Estimate\Models\EstimateLineFichaResource;

include_once base_path('packages/Rkesa/Email/vendor/afterlogic_webmail/libraries/afterlogic/api.php');

class InvoiceEmailController extends Controller
{
    public function generate(Request $request)
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

                $service = Service::find($request['service_id']);
                $estimate = null;
                $master_estimate_number = '';

                if ($service->master_estimate_id) {
                    $estimate = Estimate::find($service->master_estimate_id);
                    $master_estimate_number = $estimate->get_estimate_number();
                }

                $template = EmailTemplate::find($request['selected_template_id']);

                $replace_from = array('{task_description}',
                    '{master_estimate_number}', '{vat_percent}', '{percent}', '{service_address}', 
                    '{client_fullname}', '{total}', '{iban}');
                $replace_to = array($service->name,
                    $master_estimate_number, $request['vat_percent'], $request['pay_stage_percent'], $service->address, 
                    self::full_name($service->client_contact), $request['total'], $request['settings']['iban']);

                $tmp_text = "<!DOCTYPE html><html><body>" . $template->html . "<br>";

                $sSubject = str_replace($replace_from, $replace_to, $template->subject);

                $sTo = $service->client_contact->client_contact_emails->first()->email;
                
                $sCc = '';
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

                $creator = new FaturaPDFCreator;
                $gs = GlobalSettings::first();
                $request['gs'] =  $gs;
                $request['ci'] = CompanyInformation::first();
                $request['currency'] = currency()->getCurrency($gs->currency)['symbol'];
                $result = $creator->render_pdf($request);
                $gs->invoice_increment++;
                $gs->save();
                broadcast(new SitesSettingsChanged());
                $file = $result['result'];
                $path = $result['path'];
                $file_name = 'Fatura_'.$estimate->get_estimate_number() . '.pdf';
                $rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString($file);
                $oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, $file_name));

                $invoice = new Invoice;
                $invoice->filename = $file_name;
                $invoice->url = $path;
                $invoice->invoice_date = Carbon::now();
                $invoice->system_entry_date = Carbon::now();
                $invoice->invoice_no = 'FA '.Carbon::now()->year.'/'.($gs->invoice_increment - 1);
                $invoice->gross_total = $request->total;
                $invoice->hash = $invoice->invoice_date->toDateString().';'.$invoice->system_entry_date->toDateTimeLocalString().';'.$invoice->invoice_no.';'.$invoice->gross_total.';';
                
                $openSslHelper = new OpenSslHelper;
                $signed = $openSslHelper->sign($invoice->hash);
                $invoice->signature = $signed;
                
                $invoice->service_id = $service->id;
                $invoice->estimate_id = $service->master_estimate_id;
                $invoice->client_contact_id = $service->client_contact_id;
                $client_contact = ClientContact::find($service->client_contact_id);
                $invoice->client_id = $client_contact->client_id;
                $invoice->pay_stage_id = $request['pay_stage_id'];
                $invoice->save();

                $tmp_text = $tmp_text . "</body></html>";
                $sText = str_replace($replace_from, $replace_to, $tmp_text) . '<br><br>' . $oAccount->Signature;
                $sTextConverted = \MailSo\Base\HtmlUtils::ConvertHtmlToPlain($sText);
                $oMessage->AddText($sTextConverted, false);

                $mFoundDataURL = array();
                $aFoundedContentLocationUrls = array();
                $aFoundCids = array();

                $htmlTextConverted = \MailSo\Base\HtmlUtils::BuildHtml($sText, $aFoundCids, $mFoundDataURL, $aFoundedContentLocationUrls);
                $oMessage->AddText($htmlTextConverted, true);

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
                $res->filename = $file_name;
                $res->path = $path;
            } else {
                $res->errcode = 1;
                $res->errmess = "Email API error";
            }
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
}
