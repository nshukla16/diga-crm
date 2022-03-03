<?php

namespace App\Console\Commands;

use App\Call;
use App\User;
use Exception;
use App\Module;
use App\Setting;
use Carbon\Carbon;
use App\EmailTemplate;
use App\GlobalSettings;
use App\CompanyInformation;
use Illuminate\Console\Command;
use Rkesa\Service\Models\Service;
use Illuminate\Support\Facades\Log;
use Rkesa\Estimate\Models\Estimate;
use App\Events\SitesSettingsChanged;
use App\Notifications\NewMissedCall;
use Rkesa\Client\Http\Helpers\FaturaPDFCreator;
use Rkesa\Estimate\Models\EstimatePayStage;

include_once base_path('packages/Rkesa/Email/vendor/afterlogic_webmail/libraries/afterlogic/api.php');

class SendInvoiceEmails extends Command
{
    protected $signature = 'send:paystages';

    protected $description = 'Sending automatic emails in pay stages';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {

            $now = Carbon::today();
            $estimate_pay_stages = EstimatePayStage::with(['estimate'])->whereDate('payment_date', $now)->whereNotNull('email_template_id')->get();
            $gs = GlobalSettings::first();
            $company_settings = CompanyInformation::first();

            foreach($estimate_pay_stages as $estimate_pay_stage){
                if (class_exists('CApi') && \CApi::IsValid()) {
                    $sEmail = $gs->invoice_auto_send_email;
                    $sPassword = $gs->invoice_auto_send_email_password;
    
                    $oApiIntegratorManager = \CApi::Manager('integrator');
                    $oAccount = $oApiIntegratorManager->LoginToAccount($sEmail, $sPassword);
    
                    if ($oAccount) {
                        $oApiIntegratorManager->SetAccountAsLoggedIn($oAccount);
                    }
    
                    $service = Service::find($estimate_pay_stage->estimate->service_id);
                    $estimate = null;
                    $master_estimate_number = '';
    
                    if ($service->master_estimate_id) {
                        $estimate = Estimate::with('estimate_pay_stages')->find($service->master_estimate_id);
                        $master_estimate_number = $estimate->get_estimate_number();
                    }
    
                    $template = EmailTemplate::find($estimate_pay_stage->email_template_id);

                    $userIndex = $estimate->estimate_pay_stages->search(function($ps) use ($estimate_pay_stage) {
                        return $ps->id === $estimate_pay_stage->id;
                    });
    
                    $replace_from = array('{task_description}',
                        '{master_estimate_number}', '{vat_percent}', '{percent}', '{service_address}', 
                        '{client_fullname}', '{total}', '{iban}');
                    $replace_to = array($service->name,
                        $master_estimate_number, $this->vat_percent($estimate, $estimate_pay_stage->vat_type, $userIndex), $estimate_pay_stage->percent, $service->address, 
                        self::full_name($service->client_contact), $estimate->price + $this->calculate_vat($estimate, $estimate_pay_stage->percent, $estimate_pay_stage->vat_type, $userIndex), $company_settings->iban);
    
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
    
                    // $creator = new FaturaPDFCreator;                    
                    // $request['gs'] =  $gs;
                    // $request['ci'] = CompanyInformation::first();
                    // $request['currency'] = currency()->getCurrency($gs->currency)['symbol'];
                    // $result = $creator->render_pdf($request);
                    // $gs->invoice_increment++;
                    // $gs->save();
                    // broadcast(new SitesSettingsChanged());
                    // $file = $result['result'];
                    // $path = $result['path'];
                    // $file_name = 'Fatura_'.$estimate->get_estimate_number() . '.pdf';
                    // $rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString($file);
                    // $oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, $file_name));
    
                    $tmp_text = $tmp_text . "</body></html>";
                    $sText = str_replace($replace_from, $replace_to, $tmp_text) . '<br><br>' . $oAccount->Signature;
                    $sTextConverted = \MailSo\Base\HtmlUtils::ConvertHtmlToPlain($sText);
                    $oMessage->AddText($sTextConverted, false);
    
                    $mFoundDataURL = array();
                    $aFoundedContentLocationUrls = array();
                    $aFoundCids = array();
    
                    $htmlTextConverted = \MailSo\Base\HtmlUtils::BuildHtml($sText, $aFoundCids, $mFoundDataURL, $aFoundedContentLocationUrls);
                    $oMessage->AddText($htmlTextConverted, true);
    
                    $sSentFolder = "Sent Items";
                    $oFolders = $oApiMailManager->getFolders($oAccount);
                    $aFolders = array();
                    $oFolders->foreachWithSubFolders(function ($oFolder) use (&$aFolders, &$sSentFolder) {
                        if ($oFolder->getType() === \EFolderType::Sent) {
                            $sSentFolder = $oFolder->getFullName();
                        }
                    });
                    $sSentFolder = mb_convert_encoding($sSentFolder, "UTF7-IMAP", "UTF-8");
                    $res = $oApiMailManager->sendMessage($oAccount, $oMessage, null, $sSentFolder);
    
                } else {
                    throw new Exception('email API error');
                }
            }

        } catch (Exception $e) {
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
    }

    public function vat_percent($estimate, $vat_type, $index){
        switch($vat_type){
            case 1:
                return '0 ';
            case 2:
                return '6 ';
            case 3:
                return '23 ';
            case 4:
                return '6 & 23 ';
        }

        if ($estimate->vat_type===5 || $estimate->vat_type===2){
            return '0 ';
        }
        if ($estimate->vat_type===3){
            return '23 ';
        }

        if ($estimate->vat_type===1 && $index === 0){
            return '6 ';
        }
        if ($estimate->vat_type===1 && $index === 1){
            return '23 ';
        }
        if ($estimate->vat_type===1 && $index === 2){
            return '6 ';
        }
        if ($estimate->vat_type===1 && $index === 3){
            return '6 ';
        }

        return '0 ';
    }

    public function calculate_vat($estimate, $percent, $vat_type, $index){
        if ($estimate->vat_type===5 || $estimate->vat_type===2){
            return 0;
        }
        
        $value = $estimate->price * ($percent / 100);

        if ($estimate->vat_type===3){
            return $value * 0.23;
        }
        
        if ($estimate->vat_type === 1){
            switch ($index){
                case 0:
                    return $value * 0.06;
                case 1:
                    return $value * 0.23;
                case 2:
                    return $value * 0.06;
                case 3:
                    return $value * 0.06;
            }
        }   
        
        switch ($vat_type){
            case 1:
                return $value;
            case 2:
                return $value * 0.06;
            case 3:
                return $value * 0.23;
            case 4:
                $labor_v = $value * ($estimate->vat_maodeobra / 100) * 0.06;
                $material_v = $value * ($estimate->vat_material / 100) * 0.23;
                return $labor_v + $material_v;
        }
    
        return 0;
    }

    private function full_name($card) {
        return ($card['name'] ? $card['name'] : '') . ' ' . ($card['surname'] ? $card['surname'] : '');
    }
}
