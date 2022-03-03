<?php

namespace App\Http\Controllers;

use stdClass;
use Exception;
use UrlSigner;
use App\Invoice;
use App\VatType;
use DOMDocument;
use Carbon\Carbon;
use App\InvoiceItem;
use App\MovementType;
use SimpleXMLElement;
use App\EmailTemplate;
use App\GlobalSettings;
use App\PaymentCondition;
use App\CompanyInformation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Service\Models\Service;
use Rkesa\Expences\Models\Expence;
use Illuminate\Support\Facades\Log;
use App\Events\SitesSettingsChanged;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Rkesa\Client\Http\Helpers\OpenSslHelper;
use Rkesa\Client\Http\Helpers\FaturaPDFCreator;
use App\InvoiceDocumentType;
use App\InvoiceSeries;
use Dingo\Api\Auth\Auth as AuthAuth;
use Rkesa\Client\Http\Helpers\SaftBuilder;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactEmail;
use Rkesa\Client\Models\ClientContactPhone;
use App\User;
use App\Product;
use Illuminate\Support\Str;
use Rkesa\Estimate\Models\EstimateUnit;

class InvoiceController extends Controller
{
    public function export(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = User::where('sub', $request['sub'])->firstOrFail();

            $invoice = new Invoice;
            $invoice->invoice_date = Carbon::now();
            $invoice->system_entry_date = Carbon::now();

            $document_type = InvoiceDocumentType::where('code', $request['document_type_code'])->firstOrFail();
            $invoice->invoice_no = $document_type->code . ' ' . Carbon::now()->year() . '/' . $document_type->increment;
            $document_type->increment += 1;
            $document_type->save();

            $invoice->gross_total = $request['gross_total'];
            $invoice->gross_total_without_vat = $request['gross_total_without_vat'];

            $client = Client::where('nif', $request['nif'])->first();
            if ($client == null) {
                $contact = ClientContact::where('nif', $request['nif'])->first();
                if ($contact == null) {
                    $contact = new ClientContact();
                    $contact->name = $request['client']['name'];
                    $contact->surname = $request['client']['surname'];
                    $contact->nif = $request['client']['nif'];
                    $contact->address = $request['client']['address'];
                    $contact->postal_code = $request['client']['postal_code'];
                    $contact->city = $request['client']['city'];
                    $contact->save();

                    $invoice->client_contact_id = $contact->id;

                    $contact_email = new ClientContactEmail();
                    $contact_email->email = $request['client']['email'];
                    $contact_email->client_contact_id = $contact->id;
                    $contact_email->save();

                    $contact_phone = new ClientContactPhone();
                    $contact_phone->phone_number = $request['client']['phone'];
                    $contact_phone->client_contact_id = $contact->id;
                    $contact_phone->save();
                } else {
                    $invoice->client_contact_id = $contact->id;
                }
            } else {
                $invoice->client_id = $client->id;
            }

            $payment_condition = PaymentCondition::where('days', 15)->firstOrFail();
            $invoice->payment_condition_id = $payment_condition->id;

            $movement_type = MovementType::where('name', 'TB')->firstOrFail();
            $invoice->movement_type_id = $movement_type->id;
            $invoice->name = $request['client']['name'] . ' ' . $request['client']['surname'];
            $invoice->address = $request['client']['address'];
            $invoice->city = $request['client']['city'];
            $invoice->code = $request['client']['postal_code'];
            $invoice->nif = $request['nif'];
            $invoice->currency = 'EUR';
            $invoice->exchange = 1;
            $invoice->desc_cli = 0;
            $invoice->desc_fin = 0;
            $invoice->maturity = $request['maturity'];
            $invoice->postage = 0;
            $invoice->other_services = 0;
            $invoice->advances = 0;
            $invoice->settlement = 0;
            $invoice->is_paid = false;
            $invoice->is_final_customer = false;
            $invoice->document_type_id = $document_type->id;
            $invoice->is_valued = true;
            $invoice->creator_id = $user->id;
            $invoice->status = 'N';
            $invoice->global_discount = 0;

            $invoice->hash = Carbon::parse($invoice->invoice_date)->toDateString() . ';' . $invoice->system_entry_date->toDateTimeLocalString() . ';' . $invoice->invoice_no . ';' . number_format($invoice->gross_total, 2, '.', '') . ';';
            $previous_document = Invoice::where('document_type_id', $invoice->document_type_id)->orderBy('id', 'DESC')->first();
            if ($previous_document != null) {
                $invoice->hash = $invoice->hash . $previous_document->signature;
            }
            $openSslHelper = new OpenSslHelper;
            $signed = $openSslHelper->sign($invoice->hash);
            $invoice->signature = $signed;
            $invoice->save();

            foreach ($request['invoice_items'] as $it) {
                $invoice_item = new InvoiceItem();
                $invoice_item->description = $it['description'];
                $invoice_item->quantity = $it['quantity'];
                $invoice_item->unit_price = $it['unit_price'];
                $invoice_item->discount = $it['discount'];
                $invoice_item->unit = $it['unit'];
                $vat_type = VatType::where('percent', $it['percent'])->firstOrFail();
                $invoice_item->vat_type_id = $vat_type->id;
                $product = Product::where('name', $it['product'])->first();
                if ($product == null) {
                    $product = new Product();
                    $product->code = Str::uuid();
                    $product->name = $it['product'];
                    $product->quantity = 1;
                    $product->price = $it['unit_price'];

                    $unit = EstimateUnit::where('measure', $it['unit'])->firstOrCreate();
                    $product->estimate_unit_id = $unit->id;
                    $product->vat_type_id = $invoice_item->vat_type_id;
                    $product->category = 'Serviço';
                    $product->save();
                }
                $invoice_item->product_id = $product->id;
                $invoice_item->invoice_id = $invoice->id;
                $invoice_item->save();
            }

            $invoice_d = Invoice::with(['service', 'parent', 'invoice_items.vat_type', 'invoice_items.product', 'payment_condition', 'movement_type', 'vat_exemption_reason'])->findOrFail($invoice->id);

            $creator = new FaturaPDFCreator;
            $vt = VatType::all();
            $gs = GlobalSettings::first();
            $ci = CompanyInformation::first();

            $result = $creator->render_pdf($invoice_d, $invoice->invoice_items, $gs, $ci, $vt);
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Factura ' . $invoice->invoice_no . '.pdf"',
                'Accept-Ranges' => 'bytes',
                'Content-Length' => strlen($result)
            ];
            return Response($result, 200, $headers);
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function index(Request $request)
    {
        $client_id = intval($request->input('client_id', '0'));
        $client_contact_id = intval($request->input('client_contact_id', '0'));
        $service_id = intval($request->input('service_id', '0'));
        $estimate_id = intval($request->input('estimate_id', '0'));
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $source = $request->input('source', '');

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == '') {
            $sort = 'created_at';
        }
        $order = $request->input('order', 'desc');
        if ($order == '') {
            $order = 'desc';
        }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {
            $invoices = Invoice::with(['children', 'invoice_document_type', 'invoice_items.product', 'client', 'client_contact', 'service', 'estimate.service', 'pay_stage'])/*->where('parent_invoice_id', null)*/->select($fields_array);
            if ($client_id != 0) {
                $invoices->where('client_id', $client_id);
            }
            if ($client_contact_id != 0) {
                $invoices->where('client_contact_id', $client_contact_id);
            }
            if ($service_id != 0) {
                $invoices->where('service_id', $service_id);
            }
            if ($estimate_id != 0) {
                $invoices->where('estimate_id', $estimate_id);
            }

            if ($date_from != null && $date_to != null) {
                $invoices->whereBetween('invoice_date', [Carbon::parse($date_from), Carbon::parse($date_to)]);
            }

            if ($source != '') {
                switch ($source) {
                    default:
                        break;
                    case "FT":
                    case "FR":
                        $invoices->where('invoice_no', 'like', '%GT%')->orWhere('invoice_no', 'like', '%GR%');
                        break;
                    case "ND":
                    case "NC":
                        $invoices->where('invoice_no', 'like', '%FT%')->orWhere('invoice_no', 'like', '%FR%');
                        break;
                }
            }

            $query = $request->input('query', '');
            if ($query != '') {
                $invoices->where('invoice_no', 'like', '%' . $query . '%');
            }

            $invoices->orderBy($sort, $order);

            $res->total = $invoices->count();
            $res->rows = $invoices->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $invoice = new Invoice;
            $invoice->fill($request->all());
            $invoice->is_paid = false;
            if ($invoice->is_valued == false) {
                $invoice->gross_total = 0;
                $invoice->gross_total_without_vat = 0;
            }

            $document_type = InvoiceDocumentType::where('id', $invoice->document_type_id)->first();

            //recibos are always paid
            if ($document_type->code == 'FR') {
                $invoice->is_paid = true;
            }

            $invoice->system_entry_date = Carbon::now();
            $invoice->hash = Carbon::parse($invoice->invoice_date)->toDateString() . ';' . $invoice->system_entry_date->toDateTimeLocalString() . ';' . $invoice->invoice_no . ';' . number_format($invoice->gross_total, 2, '.', '') . ';';

            $previous_document = Invoice::where('document_type_id', $invoice->document_type_id)->where('series_id', $invoice->series_id)->orderBy('id', 'DESC')->first();
            if ($previous_document != null) {
                $invoice->hash = $invoice->hash . $previous_document->signature;
            }
            $openSslHelper = new OpenSslHelper;
            $signed = $openSslHelper->sign($invoice->hash);
            $invoice->signature = $signed;
            $invoice->creator_id = Auth::user()->id;
            $invoice->status = "N";

            $invoice->save();

            $series = InvoiceSeries::where('id', $invoice->series_id)->first();
            if ($series) {
                $series->last_invoice_date = $invoice->invoice_date;
                $series->increment += 1;
                $series->save();
            }

            if ($invoice->parent_invoice_id > 0) {
                $parent_invoice = Invoice::find($invoice->parent_invoice_id);
                if ((str_contains($invoice->invoice_no, 'FS')
                        || str_contains($invoice->invoice_no, 'FT')
                        || str_contains($invoice->invoice_no, 'FR'))
                    && (str_contains($parent_invoice->invoice_no, 'GT') || str_contains($parent_invoice->invoice_no, 'GR'))
                ) {
                    $parent_invoice->status = "F";
                    $parent_invoice->save();
                }
            }

            // $gs = GlobalSettings::first();
            // $gs->invoice_increment++;
            // $gs->save();
            // broadcast(new SitesSettingsChanged());

            if ($request->filled('invoice_items')) {
                foreach ($request['invoice_items'] as $it) {
                    $invoice_item = new InvoiceItem();
                    $invoice_item->description = $it['description'];
                    $invoice_item->quantity = $it['quantity'];
                    $invoice_item->unit_price = $it['unit_price'];
                    $invoice_item->discount = $it['discount'];
                    $invoice_item->unit = $it['unit'];
                    $invoice_item->vat_type_id = $it['vat_type_id'];
                    $invoice_item->product_id = $it['product_id'];
                    $invoice_item->invoice_id = $invoice->id;
                    $invoice_item->save();
                }
            }

            $res->id = $invoice->id;

            if ($request['send_email'] == true) {
                if (class_exists('CApi') && \CApi::IsValid()) {
                    $sEmail = Auth::user()->email;
                    $sPassword = Auth::user()->email_password;

                    $oApiIntegratorManager = \CApi::Manager('integrator');
                    $oAccount = $oApiIntegratorManager->LoginToAccount($sEmail, $sPassword);

                    if ($oAccount) {
                        $oApiIntegratorManager->SetAccountAsLoggedIn($oAccount);
                    }

                    $template = EmailTemplate::find($request['selected_template_id']);
                    $ci = CompanyInformation::first();
                    $sTo = '';

                    $replace_from = array(
                        '{task_description}',
                        '{estimate_number}', '{service_address}',
                        '{client_fullname}', '{total}', '{iban}'
                    );
                    $service_name = '';
                    $master_estimate_number = '';
                    if ($request['service_id'] > 0) {
                        $service = Service::with(['client_contact.client_contact_emails'])->find($request['service_id']);
                        $service_name = $service->name;
                        $master_estimate_number = $service->estimate_number;
                        if ($service->client_contact != null) {
                            $sTo = $service->client_contact->client_contact_emails->first()->email;
                        }
                    }
                    $replace_to = array(
                        $service_name,
                        $master_estimate_number, $request['address'],
                        $request['name'], $request['gross_total'], $ci->iban
                    );

                    $tmp_text = "<!DOCTYPE html><html><body>" . $template->html . "<br>";

                    $sSubject = str_replace($replace_from, $replace_to, $template->subject);

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

                    $invoice_d = Invoice::with(['service', 'parent', 'invoice_items.vat_type', 'invoice_items.product', 'payment_condition', 'movement_type', 'vat_exemption_reason'])->findOrFail($invoice->id);

                    $creator = new FaturaPDFCreator;
                    $vt = VatType::all();
                    $gs = GlobalSettings::first();

                    $result = $creator->render_pdf($invoice_d, $invoice->invoice_items, $gs, $ci, $vt);
                    $rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString($result);
                    $oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, $invoice->invoice_no . '.pdf'));

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
                } else {
                    $res->errcode = 1;
                    $res->errmess = "Email API error";
                }
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $invoice = Invoice::find($id);
            $invoice->fill($request->all());

            // $invoice->system_entry_date = Carbon::now();
            $invoice->hash = Carbon::parse($invoice->invoice_date)->toDateString() . ';' . Carbon::parse($invoice->system_entry_date)->toDateTimeLocalString() . ';' . $invoice->invoice_no . ';' . number_format($invoice->gross_total, 2, '.', '') . ';';

            $openSslHelper = new OpenSslHelper;
            $signed = $openSslHelper->sign($invoice->hash);
            $invoice->signature = $signed;

            $invoice->save();


            if ($request->filled('invoice_items')) {
                foreach ($request['invoice_items'] as $it) {
                    $invoice_item = new InvoiceItem();
                    if ($it['id'] != null) {
                        $invoice_item = InvoiceItem::find($it['id']);
                    }
                    $invoice_item->description = $it['description'];
                    $invoice_item->quantity = $it['quantity'];
                    $invoice_item->unit_price = $it['unit_price'];
                    $invoice_item->discount = $it['discount'];
                    $invoice_item->unit = $it['unit'];
                    $invoice_item->vat_type_id = $it['vat_type_id'];
                    $invoice_item->invoice_id = $invoice->id;
                    $invoice_item->save();
                }
            }

            if ($request->filled('removed_invoice_items')) {
                foreach ($request['removed_invoice_items'] as $rit) {
                    if ($rit > 0) {
                        $invoice_item = InvoiceItem::find($rit);
                        $invoice_item->delete();
                    }
                }
            }

            if ($request['send_email'] == true) {
                if (class_exists('CApi') && \CApi::IsValid()) {
                    $sEmail = Auth::user()->email;
                    $sPassword = Auth::user()->email_password;

                    $oApiIntegratorManager = \CApi::Manager('integrator');
                    $oAccount = $oApiIntegratorManager->LoginToAccount($sEmail, $sPassword);

                    if ($oAccount) {
                        $oApiIntegratorManager->SetAccountAsLoggedIn($oAccount);
                    }

                    $template = EmailTemplate::find($request['selected_template_id']);
                    $ci = CompanyInformation::first();
                    $sTo = '';

                    $replace_from = array(
                        '{task_description}',
                        '{estimate_number}', '{service_address}',
                        '{client_fullname}', '{total}', '{iban}'
                    );
                    $service_name = '';
                    $master_estimate_number = '';
                    if ($request['service_id'] > 0) {
                        $service = Service::with(['client_contact.client_contact_emails'])->find($request['service_id']);
                        $service_name = $service->name;
                        $master_estimate_number = $service->estimate_number;
                        if ($service->client_contact != null) {
                            $sTo = $service->client_contact->client_contact_emails->first()->email;
                        }
                    }
                    $replace_to = array(
                        $service_name,
                        $master_estimate_number, $request['address'],
                        $request['name'], $request['gross_total'], $ci->iban
                    );

                    $tmp_text = "<!DOCTYPE html><html><body>" . $template->html . "<br>";

                    $sSubject = str_replace($replace_from, $replace_to, $template->subject);

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

                    $invoice_d = Invoice::with(['service', 'parent', 'invoice_items.vat_type', 'invoice_items.product', 'payment_condition', 'movement_type', 'vat_exemption_reason'])->findOrFail($invoice->id);

                    $creator = new FaturaPDFCreator;
                    $vt = VatType::all();
                    $gs = GlobalSettings::first();
                    $result = $creator->render_pdf($invoice_d, $invoice->invoice_items, $gs, $ci, $vt);
                    $rResource = \MailSo\Base\ResourceRegistry::CreateMemoryResourceFromString($result);
                    $oMessage->Attachments()->Add(\MailSo\Mime\Attachment::NewInstance($rResource, $invoice->invoice_no . '.pdf'));

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
                } else {
                    $res->errcode = 1;
                    $res->errmess = "Email API error";
                }
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function show(Request $request, $id)
    {
        return Invoice::with(['invoice_document_type', 'invoice_items', 'client', 'client_contact', 'service', 'estimate.estimate_pay_stages', 'pay_stage'])->findOrFail($id);
    }

    public function get_pdf_link(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->cando('invoices', 'read')) {
            return response('forbidden', 403);
        }
        return response()->json(['link' => UrlSigner::sign(env('APP_URL') . '/api/invoices/pdf/' . $id, Carbon::now()->addHours(9))]);
    }

    public function pdf(Request $request, $id)
    {
        //if (UrlSigner::validate(url()->full())) {

        $invoice = Invoice::with(['service', 'parent', 'invoice_items.vat_type', 'invoice_items.product', 'payment_condition', 'movement_type', 'vat_exemption_reason'])->findOrFail($id);

        $creator = new FaturaPDFCreator;
        $gs = GlobalSettings::first();
        $ci = CompanyInformation::first();
        $vt = VatType::all();
        $result = $creator->render_pdf($invoice, $invoice->invoice_items, $gs, $ci, $vt);

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $invoice->invoice_no . '.pdf"',
            'Accept-Ranges' => 'bytes',
            'Content-Length' => strlen($result)
        ];
        return Response($result, 200, $headers);
        // } else {
        //     return response('forbidden', 403);
        // }
    }

    public function destroy($id)
    {
        $invoice_items = InvoiceItem::where('invoice_id', $id)->delete();
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
    }

    public function cancel(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->is_canceled = true;
        $invoice->status = "A";

        if ($request->filled('canceling_reason')) {
            $invoice->canceling_reason = $request['canceling_reason'];
        }

        $invoice->save();
    }

    public function pay($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->is_paid = !$invoice->is_paid;

        $invoice->save();
    }

    public function get_saft(Request $request)
    {
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $saft_format = $request->input('saft_format');

        $invoices_count = Invoice::whereBetween('invoice_date', [Carbon::parse($date_from), Carbon::parse($date_to)])->count();
        if ($invoices_count == 0) {
            return response()->json(['message' => 'No invoices found'], 500);
        }

        $saftBuilder = new SaftBuilder;
        $xml = $saftBuilder->build($date_from, $date_to);

        $file_name = 'saft ' . Carbon::parse($date_from)->toDateString() . '-' . Carbon::parse($date_to)->toDateString() . '.xml';
        $response = Response::create($xml, 200);
        $response->header('Content-Type', 'text/xml');
        $response->header('Cache-Control', 'public');
        $response->header('Content-Description', 'File Transfer');
        $response->header('Content-Disposition', 'attachment; filename=' . $file_name . '');
        $response->header('Content-Transfer-Encoding', 'binary');
        return $response;
    }

    public function update_settings(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            if ($request->filled('payment_conditions')) {
                foreach ($request['payment_conditions'] as $pc) {
                    $payment_condition = new PaymentCondition();
                    if ($pc['id'] > 0) {
                        $payment_condition = PaymentCondition::find($pc['id']);
                    }
                    $payment_condition->name = $pc['name'];
                    $payment_condition->days = $pc['days'];
                    $payment_condition->save();
                }
            }

            if ($request->filled('removed_payment_conditions')) {
                foreach ($request['removed_payment_conditions'] as $rpc) {
                    if ($rpc > 0) {
                        $payment_condition = PaymentCondition::find($rpc);
                        $payment_condition->delete();
                    }
                }
            }

            if ($request->filled('movement_types')) {
                foreach ($request['movement_types'] as $mt) {
                    $movement_type = new MovementType();
                    if ($mt['id'] > 0) {
                        $movement_type = MovementType::find($mt['id']);
                    }
                    $movement_type->name = $mt['name'];
                    $movement_type->description = $mt['description'];
                    $movement_type->days = $mt['days'];
                    $movement_type->save();
                }
            }

            if ($request->filled('removed_movement_types')) {
                foreach ($request['removed_movement_types'] as $rmt) {
                    if ($rmt > 0) {
                        $movement_type = MovementType::find($rmt);
                        $movement_type->delete();
                    }
                }
            }
            $gs = GlobalSettings::first();
            $gs->invoice_notes = $request['invoice_notes'];

            $vat_exemption_reason_id = intval($request['vat_exemption_reason_id'], 0);
            if ($vat_exemption_reason_id > 0) {
                $gs->vat_exemption_reason_id = $request['vat_exemption_reason_id'];
            }

            $gs->always_use_exemption_for_invoices = $request['always_use_exemption_for_invoices'];
            if ($gs->always_use_exemption_for_invoices == false) {
                $gs->vat_exemption_reason_id = null;
            }

            $gs->save();
            broadcast(new SitesSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
