<?php

namespace Rkesa\Client\Http\Helpers;

use Exception;
use Carbon\Carbon;
use App\GlobalSettings;
use App\CompanyInformation;
use Illuminate\Support\Str;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Log;
use App\Events\SitesSettingsChanged;
use App\Invoice;
use App\VatType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Rkesa\Estimate\Models\Estimate;
use SimpleXMLElement;
use Illuminate\Support\Facades\File;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;

class SaftBuilder
{
    public function build($date_from, $date_to)
    {
        $invoices = Invoice::with(['movement_type', 'parent', 'service', 'invoice_document_type', 'invoice_items.product', 'invoice_items.vat_type', 'payment_condition', 'client', 'client_contact'])->whereBetween('invoice_date', [Carbon::parse($date_from), Carbon::parse($date_to)])->get();
        $source_invoices = [];

        $clients = $this->customer($invoices);
        $total_debit = 0.0;
        $total_credit = 0.0;

        $hash = [
            'FT' => '',
            'FS' => '',
            'FR' => '',
            'ND' => '',
            'NC' => '',
            'VD' => '',
            'TV' => '',
            'TD' => '',
            'AA' => '',
            'DA' => '',
            'RP' => '',
            'RE' => '',
            'CS' => '',
            'LD' => '',
            'RA' => '',
        ];
        foreach ($invoices as $invoice) {

            if (!in_array($invoice->invoice_document_type->code, ['FT', 'FS', 'FR', 'ND', 'NC', 'VD', 'TV', 'TD', 'AA', 'DA', 'RP', 'RE', 'CS', 'LD', 'RA'])) {
                continue;
            }

            $source_invoice = [];
            $source_invoice['InvoiceNo'] = $invoice->invoice_no;
            $source_invoice['ATCUD'] = 0;
            $source_invoice['DocumentStatus'] = [];
            $source_invoice['DocumentStatus']['InvoiceStatus'] = $invoice->status;

            $source_invoice['DocumentStatus']['InvoiceStatusDate'] = Carbon::parse($invoice->updated_at)->toDateTimeLocalString();
            $source_invoice['DocumentStatus']['SourceID'] = 'Administrador';
            if ($invoice->service) {
                $source_invoice['DocumentStatus']['SourceID'] = $invoice->service->estimate_number;
            }
            $source_invoice['DocumentStatus']['SourceBilling'] = 'P';

            $source_invoice['Hash'] = '';

            $source_invoice['HashControl'] = 1;
            $source_invoice['Period'] = 10; //$invoice->payment_condition->days;
            $source_invoice['InvoiceDate'] = $invoice->invoice_date;
            $source_invoice['InvoiceType'] = $invoice->invoice_document_type->code;
            $source_invoice['SpecialRegimes'] = [];
            $source_invoice['SpecialRegimes']['SelfBillingIndicator'] = 0;
            $source_invoice['SpecialRegimes']['CashVATSchemeIndicator'] = 0;
            $source_invoice['SpecialRegimes']['ThirdPartiesBillingIndicator'] = 0;
            $source_invoice['SourceID'] = 'Administrador';
            if ($invoice->service) {
                $source_invoice['SourceID'] = $invoice->service->estimate_number;
            }
            $source_invoice['SystemEntryDate'] = Carbon::parse($invoice->system_entry_date)->toDateTimeLocalString();

            $source_invoice['CustomerID'] = 'Consumidor Final';

            if ($invoice->is_final_consumer != true) {
                foreach ($clients as $client) {
                    if ($client['CustomerTaxID'] == $invoice->nif && $client['CompanyName'] == $invoice->name) {
                        $source_invoice['CustomerID'] = $client['CustomerID'];
                    }
                }
            }

            $vat = 0.0;
            $total = 0.0;
            $lines = [];
            $estimate = null;
            if ($invoice->service) {
                if ($invoice->service->master_estimate_id) {
                    $estimate = Estimate::with(['lines.correct_lineable'])->where('id', $invoice->service->master_estimate_id)->first();
                }
            }

            foreach ($invoice->invoice_items as $invoice_item) {

                $line = [];
                $line['LineNumber'] = $invoice->invoice_items->search(function ($ii) use ($invoice_item) {
                    return $ii->id === $invoice_item->id;
                }) + 1;

                if ($estimate) {
                    $line['OrderReferences'] = [];
                    $line['OrderReferences']['OriginatingON'] = $invoice->service->estimate_number . '/' . $invoice_item->id;
                    $line['OrderReferences']['OrderDate'] = Carbon::parse($invoice_item->created_at)->toDateString();
                }
                if ($invoice->parent_invoice_id > 0 && $source_invoice['InvoiceType'] != 'NC' && $source_invoice['InvoiceType'] != 'ND') {
                    $line['OrderReferences'] = [];
                    $line['OrderReferences']['OriginatingON'] = $invoice->parent->invoice_no;
                    $line['OrderReferences']['OrderDate'] = Carbon::parse($invoice->parent->invoice_date)->toDateString();
                }


                $line['ProductCode'] = $invoice_item->product->code;
                $line['ProductDescription'] = $invoice_item->product->name;
                $line['Quantity'] = $invoice_item->quantity;
                $line['UnitOfMeasure'] = $invoice_item->unit;
                $line['UnitPrice'] = $invoice_item->unit_price;
                if ($invoice_item->discount > 0) {
                    $line['UnitPrice'] = $invoice_item->unit_price - $invoice_item->unit_price * $invoice_item->discount / 100;
                }
                if ($invoice->global_discount > 0) {
                    $line['UnitPrice'] = $line['UnitPrice'] - $line['UnitPrice'] * $invoice->global_discount / 100;
                }

                $line['TaxPointDate'] = $invoice->invoice_date;

                if ($invoice->parent_invoice_id > 0 /* && ($source_invoice['InvoiceType'] == 'NC' || $source_invoice['InvoiceType'] == 'ND')*/) {
                    $line['References'] = [];
                    $line['References']['Reference'] = $invoice->parent->invoice_no;

                    if (strlen($invoice->correction_reason) > 0) {
                        $line['References']['Reason'] = $invoice->correction_reason;
                    }
                    $line['TaxPointDate'] = $invoice->parent->invoice_date;
                }

                $line['Description'] = $invoice_item->description;

                $value = $line['UnitPrice'] * $invoice_item->quantity;
                $total += $value;
                $vat += $value * $invoice_item->vat_type->percent / 100;
                if ($source_invoice['InvoiceType'] == 'NC') {
                    $line['DebitAmount'] = $value;
                } else {
                    $line['CreditAmount'] = $value;
                }

                if ($source_invoice['DocumentStatus']['InvoiceStatus'] == 'N') {
                    if ($source_invoice['InvoiceType'] == 'NC') {
                        $total_debit += $value;
                    } else {
                        $total_credit += $value;
                    }
                }

                $line['Tax'] = [];
                $line['Tax']['TaxType'] = 'IVA';
                $line['Tax']['TaxCountryRegion'] = 'PT';
                $line['Tax']['TaxCode'] = $invoice_item->vat_type->code;
                $line['Tax']['TaxPercentage'] = $invoice_item->vat_type->percent;
                if ($invoice_item->vat_type->percent == 0) {
                    $line['TaxExemptionReason'] = $invoice->vat_exemption_reason->name;
                    $line['TaxExemptionCode'] = $invoice->vat_exemption_reason->code;
                    $line['SettlementAmount'] = 0;
                }

                if ($invoice_item->discount > 0 || $invoice->global_discount > 0) {
                    $line['SettlementAmount'] =  $invoice_item->unit_price * $invoice_item->quantity - $value;
                }

                array_push($lines, $line);
            }

            $source_invoice['Hash'] = $invoice->signature;
            // $openSslHelper = new OpenSslHelper;
            // $sign = Carbon::parse($invoice->invoice_date)->toDateString() . ';'
            //     . Carbon::parse($invoice->system_entry_date)->toDateTimeLocalString() . ';'
            //     . $invoice->invoice_no . ';'
            //     . number_format(($total + $vat), 2, '.', '') . ';';
            // $source_invoice['Hash'] = $openSslHelper->sign($sign . $hash[$invoice->invoice_document_type->code]);
            // $hash[$invoice->invoice_document_type->code] = $source_invoice['Hash'];

            $source_invoice['Line'] = $lines;

            $source_invoice['DocumentTotals'] = [];
            $source_invoice['DocumentTotals']['TaxPayable'] = number_format($vat, 2, '.', '');
            $source_invoice['DocumentTotals']['NetTotal'] = number_format($total, 2, '.', '');
            $source_invoice['DocumentTotals']['GrossTotal'] = number_format($total + $vat, 2, '.', '');

            if ($invoice->currency != 'EUR') {
                $currency = [];
                $currency['CurrencyCode'] = $invoice->currency;
                $currency['CurrencyAmount'] = number_format($invoice->exchange * ($total + $vat), 2, '.', '');
                $currency['ExchangeRate'] = $invoice->exchange;
                $source_invoice['DocumentTotals']['Currency'] = $currency;
            }

            if ($source_invoice['InvoiceType'] != 'NC' && $invoice->is_paid == true) {
                $source_invoice['DocumentTotals']['Payment'] = [
                    'PaymentMechanism' => $invoice->movement_type->name,
                    'PaymentAmount' => number_format($invoice->gross_total, 2, '.', ''),
                    'PaymentDate' => Carbon::parse($invoice->invoice_date)->addDays($invoice->movement_type->days)->toDateString()
                ];
            }

            $settlements = [];
            // if ($invoice->desc_cli > 0) {
            //     $settlement = [];
            //     $settlement['SettlementDiscount'] = 'Desconto comercial';
            //     $settlement['SettlementAmount'] = number_format($invoice->desc_cli, 2, '.', '');
            //     $settlement['SettlementDate'] = Carbon::parse($invoice->invoice_date)->toDateString();
            //     array_push($settlements, $settlement);
            // }
            if ($invoice->desc_fin > 0) {
                $settlement = [];
                $settlement['SettlementDiscount'] = 'Desconto financeiro';
                $finance_discount = $total * $invoice->desc_fin / 100;
                $settlement['SettlementAmount'] = number_format($finance_discount, 2, '.', '');
                $settlement['SettlementDate'] = Carbon::parse($invoice->invoice_date)->toDateString();
                array_push($settlements, $settlement);
            }
            if (count($settlements) > 0) {
                $source_invoice['DocumentTotals']['Settlement'] = $settlements;
            }
            array_push($source_invoices, $source_invoice);
        }

        $tax_table_entries = [];
        $vat_types = VatType::all();
        foreach ($vat_types as $vat_type) {
            $new_tax = [];
            $new_tax['TaxType'] = 'IVA';
            $new_tax['TaxCountryRegion'] = $vat_type->country_region;
            $new_tax['TaxCode'] = $vat_type->code;
            $new_tax['Description'] = $vat_type->name;
            $new_tax['TaxPercentage'] = $vat_type->percent;

            array_push($tax_table_entries, $new_tax);
        }

        $movement_of_goods = $this->movement_of_goods($invoices, $clients);
        $source_documents = array(
            'SalesInvoices' => array(
                'NumberOfEntries' => count($source_invoices),
                'TotalDebit' => $total_debit,
                'TotalCredit' => $total_credit,
                'Invoice' => $source_invoices
            )
        );
        if ($movement_of_goods['NumberOfMovementLines'] > 0) {
            $source_documents['MovementOfGoods'] = $movement_of_goods;
        }

        $my_array = array(
            'Header' => $this->header($date_from, $date_to),
            'MasterFiles' => array(
                'Customer' => $clients,
                'Product' => $this->product($invoices),
                'TaxTable' => array(
                    'TaxTableEntry' => $tax_table_entries,
                ),
            ),
            'SourceDocuments' => $source_documents
        );

        $xml = $this->arrayToXml($my_array, '<AuditFile xmlns="urn:OECD:StandardAuditFile-Tax:PT_1.04_01" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>');
        $xml = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="Windows-1252"?>', $xml);

        return $xml;
    }

    private function movement_of_goods($invoices, $clients)
    {
        $stock_movements = [];
        $hash = [
            'GT' => '',
            'GR' => ''
        ];
        $total_quantity_issued = 0;

        foreach ($invoices as $invoice) {
            if (!in_array($invoice->invoice_document_type->code, ['GT', 'GR'])) {
                continue;
            }
            $stock_movement = [];

            $stock_movement['DocumentNumber'] = $invoice->invoice_no;
            $stock_movement['ATCUD'] = 0;
            $stock_movement['DocumentStatus'] = [];
            $stock_movement['DocumentStatus']['MovementStatus'] = $invoice->status;

            $stock_movement['DocumentStatus']['MovementStatusDate'] = Carbon::parse($invoice->updated_at)->toDateTimeLocalString();
            $stock_movement['DocumentStatus']['SourceID'] = $invoice->creator_id;
            $stock_movement['DocumentStatus']['SourceBilling'] = 'P';

            $stock_movement['Hash'] = '';

            $stock_movement['HashControl'] = 1;
            $stock_movement['Period'] = 8; //$invoice->payment_condition->days;
            $stock_movement['MovementDate'] = $invoice->invoice_date;
            $stock_movement['MovementType'] = $invoice->invoice_document_type->code;
            $stock_movement['SystemEntryDate'] = Carbon::parse($invoice->system_entry_date)->toDateTimeLocalString();

            $stock_movement['CustomerID'] = 'Consumidor Final';
            if ($invoice->is_final_consumer != true) {
                foreach ($clients as $client) {
                    if ($client['CustomerTaxID'] == $invoice->nif && $client['CompanyName'] == $invoice->name) {
                        $stock_movement['CustomerID'] = $client['CustomerID'];
                    }
                }
            }

            $stock_movement['SourceID'] = $invoice->creator_id;

            //$stock_movement['EACCode'] = '';
            $stock_movement['ShipTo'] = [];
            $stock_movement['ShipTo']['Address'] = [];
            $stock_movement['ShipTo']['Address']['AddressDetail'] = $invoice->discharge_address;
            $stock_movement['ShipTo']['Address']['City'] = $invoice->discharge_city;
            $stock_movement['ShipTo']['Address']['PostalCode'] = $invoice->discharge_postal_code;
            $stock_movement['ShipTo']['Address']['Country'] = $invoice->discharge_country;

            $stock_movement['ShipFrom'] = [];
            if (strlen($invoice->discharge_registration) > 0){
                $stock_movement['ShipFrom']['DeliveryID'] = $invoice->discharge_registration;
            }
            $stock_movement['ShipFrom']['Address'] = [];
            $stock_movement['ShipFrom']['Address']['AddressDetail'] = $invoice->loading_address;
            $stock_movement['ShipFrom']['Address']['City'] = $invoice->loading_city;
            $stock_movement['ShipFrom']['Address']['PostalCode'] = $invoice->loading_postal_code;
            $stock_movement['ShipFrom']['Address']['Country'] = $invoice->loading_country;

            $stock_movement['MovementEndTime'] = Carbon::parse($invoice->discharge_date)->toDateTimeLocalString();
            $stock_movement['MovementStartTime'] = Carbon::parse($invoice->loading_date)->toDateTimeLocalString();
            //$stock_movement['ATDocCodeID'] = '';

            $vat = 0.0;
            $total = 0.0;
            $lines = [];
            $estimate = null;
            if ($invoice->service) {
                if ($invoice->service->master_estimate_id) {
                    $estimate = Estimate::with(['lines.correct_lineable'])->where('id', $invoice->service->master_estimate_id)->first();
                }
            }

            foreach ($invoice->invoice_items as $invoice_item) {

                $line = [];
                $line['LineNumber'] = $invoice->invoice_items->search(function ($ii) use ($invoice_item) {
                    return $ii->id === $invoice_item->id;
                }) + 1;

                if ($estimate) {
                    $line['OrderReferences'] = [];
                    $line['OrderReferences']['OriginatingON'] = $invoice->service->estimate_number . '/' . $invoice_item->id;
                    $line['OrderReferences']['OrderDate'] = Carbon::parse($invoice_item->created_at)->toDateString();
                }
                if ($invoice->parent_invoice_id > 0) {
                    $line['OrderReferences'] = [];
                    $line['OrderReferences']['OriginatingON'] = $invoice->parent->invoice_no;
                    $line['OrderReferences']['OrderDate'] = Carbon::parse($invoice->parent->invoice_date)->toDateString();
                }

                $line['ProductCode'] = $invoice_item->product->code;
                $line['ProductDescription'] = $invoice_item->product->name;
                $line['Quantity'] = $invoice_item->quantity;
                $total_quantity_issued += $invoice_item->quantity;
                $line['UnitOfMeasure'] = $invoice_item->unit;
                $line['UnitPrice'] = $invoice_item->unit_price;
                if ($invoice_item->discount > 0) {
                    $line['UnitPrice'] = $invoice_item->unit_price - $invoice_item->unit_price * $invoice_item->discount / 100;
                }
                if ($invoice->global_discount > 0) {
                    $line['UnitPrice'] = $line['UnitPrice'] - $line['UnitPrice'] * $invoice->global_discount / 100;
                }
                if ($invoice->is_valued == false) {
                    $line['UnitPrice'] = 0.0;
                }

                $line['Description'] = $invoice_item->description;

                if ($invoice->is_valued != false) {
                    $value = $line['UnitPrice'] * $invoice_item->quantity;
                    $total += $value;
                    $vat += $value * $invoice_item->vat_type->percent / 100;
                    $line['CreditAmount'] = $value;

                    $line['Tax'] = [];
                    $line['Tax']['TaxType'] = 'IVA';
                    $line['Tax']['TaxCountryRegion'] = 'PT';
                    $line['Tax']['TaxCode'] = $invoice_item->vat_type->code;
                    $line['Tax']['TaxPercentage'] = $invoice_item->vat_type->percent;
                    if ($invoice_item->vat_type->percent == 0) {
                        $line['TaxExemptionReason'] = $invoice->vat_exemption_reason->name;
                        $line['TaxExemptionCode'] = $invoice->vat_exemption_reason->code;
                        $line['SettlementAmount'] = 0;
                    }

                    if ($invoice_item->discount > 0 || $invoice->global_discount > 0) {
                        $line['SettlementAmount'] =  $invoice_item->unit_price * $invoice_item->quantity - $value;
                    }
                } else {
                    $line['CreditAmount'] = 0.0;
                }

                array_push($lines, $line);
            }


            $stock_movement['Hash'] = $invoice->signature;
            // $openSslHelper = new OpenSslHelper;
            // $sign = Carbon::parse($invoice->invoice_date)->toDateString() . ';'
            //     . Carbon::parse($invoice->system_entry_date)->toDateTimeLocalString() . ';'
            //     . $invoice->invoice_no . ';'
            //     . number_format($invoice->is_valued == false ? 0 : ($total + $vat), 2, '.', '') . ';';
            // $stock_movement['Hash'] = $openSslHelper->sign($sign . $hash[$invoice->invoice_document_type->code]);
            // $hash[$invoice->invoice_document_type->code] = $stock_movement['Hash'];

            $stock_movement['Line'] = $lines;

            $stock_movement['DocumentTotals'] = [];
            if ($invoice->is_valued != false) {
                $stock_movement['DocumentTotals']['TaxPayable'] = number_format($vat, 2, '.', '');
                $stock_movement['DocumentTotals']['NetTotal'] = number_format($total, 2, '.', '');
                $stock_movement['DocumentTotals']['GrossTotal'] = number_format($total + $vat, 2, '.', '');
            } else {
                $stock_movement['DocumentTotals']['TaxPayable'] = '0.00';
                $stock_movement['DocumentTotals']['NetTotal'] = '0.00';
                $stock_movement['DocumentTotals']['GrossTotal'] = '0.00';
            }

            $settlements = [];
            // if ($invoice->desc_cli > 0) {
            //     $settlement = [];
            //     $settlement['SettlementDiscount'] = 'Desconto comercial';
            //     $settlement['SettlementAmount'] = number_format($invoice->desc_cli, 2, '.', '');
            //     $settlement['SettlementDate'] = Carbon::parse($invoice->invoice_date)->toDateString();
            //     array_push($settlements, $settlement);
            // }
            if ($invoice->desc_fin > 0) {
                $settlement = [];
                $settlement['SettlementDiscount'] = 'Desconto financeiro';
                $finance_discount = $total * $invoice->desc_fin / 100;
                $settlement['SettlementAmount'] = number_format($finance_discount, 2, '.', '');
                $settlement['SettlementDate'] = Carbon::parse($invoice->invoice_date)->toDateString();
                array_push($settlements, $settlement);
            }
            if (count($settlements) > 0) {
                $stock_movement['DocumentTotals']['Settlement'] = $settlements;
            }

            array_push($stock_movements, $stock_movement);
        }

        return array(
            'NumberOfMovementLines' => count($stock_movements),
            'TotalQuantityIssued' => $total_quantity_issued,
            'StockMovement' => $stock_movements
        );
    }

    private function product($invoices)
    {
        $products = [];
        foreach ($invoices as $invoice) {
            foreach ($invoice->invoice_items as $invoice_item) {

                $new_product = [];
                $p_exists = false;

                foreach ($products as $product) {
                    if ($product['ProductCode'] == $invoice_item->product->code) {
                        $p_exists = true;
                    }
                }
                if ($p_exists == false) {

                    $new_product['ProductType'] = 'P';
                    $new_product['ProductCode'] = $invoice_item->product->code;
                    $new_product['ProductGroup'] = $invoice_item->product->category;
                    $new_product['ProductDescription'] = $invoice_item->product->name;
                    $new_product['ProductNumberCode'] = $invoice_item->product->code;

                    array_push($products, $new_product);
                }
            }
        }

        return $products;
    }

    private function customer($invoices)
    {
        $clients = [];

        foreach ($invoices as $invoice) {

            if ($invoice->nif == '' || $invoice->nif == null) {
                continue;
            }

            $new_client = [];
            $exists = false;


            $billing_address = [
                'AddressDetail' => $invoice->address == '' ? 'Desconhecido' : $invoice->address,
                'City' => $invoice->city == '' ? 'Desconhecido' : $invoice->city,
                'PostalCode' => $invoice->code == '' ? 'Desconhecido' : $invoice->code,
                'Country' => 'PT'
            ];
            $ship_to_address = [
                'AddressDetail' => $invoice->discharge_address,
                'City' => $invoice->discharge_city,
                'PostalCode' => $invoice->discharge_postal_code,
                'Country' => $invoice->discharge_country,
            ];

            if ($invoice->is_final_consumer == true) {
                $new_client['CustomerID'] = 'Consumidor Final';
                $new_client['AccountID'] = 'Desconhecido';
                $new_client['CustomerTaxID'] = '999999990';
                $new_client['CompanyName'] = 'Consumidor Final';

                $billing_address['AddressDetail'] = 'Desconhecido';
                $billing_address['City'] = 'Desconhecido';
                $billing_address['PostalCode'] = 'Desconhecido';
                $new_client['BillingAddress'] = $billing_address;
                if (strlen($invoice->discharge_address) > 0) {
                    $new_client['ShipToAddress'] = $ship_to_address;
                }
            } else {
                $new_client['CustomerID'] = '';
                $new_client['AccountID'] = 'Desconhecido';
                $new_client['CustomerTaxID'] = $invoice->nif;
                $new_client['CompanyName'] = $invoice->name;

                if ($invoice->client_contact_id > 0) {
                    $client_contact = ClientContact::with(['client_contact_emails', 'client_contact_phones'])->find($invoice->client_contact_id);
                    $new_client['CustomerID'] = 'cc_' . $invoice->client_contact_id;
                    $new_client['Contact'] = strlen($client_contact->name) > 50 ? substr($client_contact->name, 0, 47) . "..." : $client_contact->name;
                    $new_client['BillingAddress'] = $billing_address;
                    if (strlen($invoice->discharge_address) > 0) {
                        $new_client['ShipToAddress'] = $ship_to_address;
                    }

                    if (count($client_contact->client_contact_phones) > 0) {
                        $new_client['Telephone'] = $client_contact->client_contact_phones[0]->phone_number;
                    }
                    if (count($client_contact->client_contact_emails) > 0) {
                        $new_client['Email'] = $client_contact->client_contact_emails[0]->email;
                    }
                } 
                if ($invoice->client_id > 0) {
                    $client = Client::find($invoice->client_id);
                    $new_client['BillingAddress'] = $billing_address;
                    if (strlen($client->site) > 0){
                        $new_client['Website'] = $client->site;
                    }
                    if ($invoice->client_contact_id > 0){
                        $new_client['CustomerID'] = 'cccl_' . $invoice->client_contact_id . '_' . $invoice->client_id;
                    }
                    else{
                        $new_client['CustomerID'] = 'cl_' . $invoice->client_id;
                    }
                }
            }

            $new_client['SelfBillingIndicator'] = 0;

            foreach ($clients as $client) {
                if ($client['CustomerID'] == $new_client['CustomerID']) {
                    $exists = true;
                }
            }
            if ($exists == false) {
                array_push($clients, $new_client);
            }
        }

        return $clients;
    }

    private function header($date_from, $date_to)
    {
        $ci = CompanyInformation::first();

        $version = '';
        try {
            $version = File::get(base_path() . '/VERSION');
        } catch (Exception $exception) {
        }

        return array(
            'AuditFileVersion' => '1.04_01',
            'CompanyID' => $ci->crc . ' ' . $ci->crc_number,
            'TaxRegistrationNumber' => $ci->tax_number,
            'TaxAccountingBasis' => 'P',
            'CompanyName' => $ci->name,
            'BusinessName' => $ci->name,
            'CompanyAddress' => array(
                'AddressDetail' => $ci->address,
                'City' => $ci->city,
                'PostalCode' => $ci->postal_code,
                'Country' => 'PT'
            ),
            'FiscalYear' => Carbon::parse($date_from)->year,
            'StartDate' => $date_from,
            'EndDate' => $date_to,
            'CurrencyCode' => 'EUR',
            'DateCreated' => Carbon::now()->toDateString(),
            'TaxEntity' => 'Global',
            'ProductCompanyTaxID' => '514198460',
            'SoftwareCertificateNumber' => '00000',
            'ProductID' => 'Diga ERP/ODIGA LDA',
            'ProductVersion' => $version == '' ? '1.0' : $version,
            'Email' => Auth::user()->email
        );
    }

    function arrayToXml($array, $rootElement = null, $xml = null)
    {
        $_xml = $xml;
        if ($_xml === null) {
            $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
        }
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $keys = array_keys($v);
                if (array_filter($keys, 'is_int') == true) {
                    foreach ($v as $item_k => $item) {

                        if (is_array($item)) {
                            $this->arrayToXml($item, $k, $_xml->addChild($k));
                        } else {
                            $_xml->addChild($k, htmlspecialchars($item));
                        }
                    }
                } else {
                    $this->arrayToXml($v, $k, $_xml->addChild($k));
                }
            } else {
                $_xml->addChild($k, htmlspecialchars($v));
            }
        }
        return $_xml->asXML();
    }
}
