<?php

namespace Rkesa\Client\Http\Helpers;

use Exception;
use Carbon\Carbon;
use NumberFormatter;
use App\GlobalSettings;
use App\CompanyInformation;
use Illuminate\Support\Str;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Log;
use App\Events\SitesSettingsChanged;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Illuminate\Support\Facades\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FaturaPDFCreator
{

    public function render_pdf($invoice, $invoice_items, $global_settings, $company_information, $vat_types)
    {

        $pdf = new Pdf(array(
            'no-outline',         // Make Chrome not complain
            'margin-right'  => 0,
            'margin-left'   => 0,
            'header-spacing' => 2,
            'orientation' => 'portrait'
        ));

        $pdf->addPage($this->render($invoice, $invoice_items, $global_settings, $company_information, $vat_types));

        $result = $pdf->toString();
        if ($result === false) {
            throw new Exception('Could not create PDF: ' . $pdf->getError());
        }

        return $result;
    }

    private function render_qr($invoice, $invoice_items, $global_settings, $company_information, $vat_types)
    {
        $text = 'A:' . $company_information->tax_number . '*';
        $text = $text . 'B:' . $invoice->nif . '*';
        $text = $text . 'C:PT*';
        $text = $text . 'D:' . $invoice->invoice_document_type->code . '*';
        $text = $text . 'E:' . $invoice->status . '*';
        $text = $text . 'F:' . Carbon::parse($invoice->invoice_date)->format('Ymd') . '*';
        $text = $text . 'G:' . $invoice->invoice_no . '*';
        $text = $text . 'H:' . 0 . '*'; // ATCUD TODO
        $text = $text . 'I1:PT*';

        $exempt = 'I2:0.00*';
        $reduced = 'I3:0.00*';
        $reduced_vat = 'I4:0.00*';
        $intermediate = 'I5:0.00*';
        $intermediate_vat = 'I6:0.00*';
        $normal = 'I7:0.00*';
        $normal_vat = 'I8:0.00*';
        $vat_table_values = $this->calculate_vats($invoice, $invoice_items, $vat_types);
        foreach ($vat_table_values as $vat_table_value) {
            if ($vat_table_value['code'] == 'ISE') {
                $exempt = 'I2:' . number_format($vat_table_value['value'], 2) . '*';
                $text = $text . $exempt;
            }
            if ($vat_table_value['code'] == 'RED') {
                $reduced = 'I3:' . number_format($vat_table_value['value'], 2) . '*';
                $reduced_vat = 'I4:' . number_format($vat_table_value['vat_value'], 2) . '*';
                $text = $text . $reduced;
                $text = $text . $reduced_vat;
            }
            if ($vat_table_value['code'] == 'INT') {
                $intermediate = 'I5:' . number_format($vat_table_value['value'], 2) . '*';
                $intermediate_vat = 'I6:' . number_format($vat_table_value['vat_value'], 2) . '*';
                $text = $text . $intermediate;
                $text = $text . $intermediate_vat;
            }
            if ($vat_table_value['code'] == 'NOR') {
                $normal = 'I7:' . number_format($vat_table_value['value'], 2) . '*';
                $normal_vat = 'I8:' . number_format($vat_table_value['vat_value'], 2) . '*';
                $text = $text . $normal;
                $text = $text . $normal_vat;
            }
        }

        $totals = $this->calculate_totals($invoice, $invoice_items);
        $text = $text . 'N:' . number_format($totals['vat'], 2) . '*';
        $text = $text . 'O:' . number_format($totals['vat'] + $totals['total'], 2) . '*';


        $text = $text . 'Q:' . $this->get_signature_characters($invoice) . '*';
        $text = $text . 'R:0000*';
        $text = $text . 'S:' . $invoice->movement_type->name . ';' . str_replace(' ', '', $company_information->iban);

        $qr = '<div class="qrcode">' . QrCode::errorCorrection('M')->format('svg')->encoding('UTF-8')->generate($text) . '</div>';

        return $qr;
    }

    private function get_signature_characters($invoice)
    {
        return $invoice->signature[0] . $invoice->signature[10] . $invoice->signature[20] . $invoice->signature[30];
    }

    private function calculate_totals($invoice, $invoice_items)
    {
        $total_liquid = 0.0;
        $total = 0.0;
        $vat = 0.0;
        $commercial_discount = 0.0;
        $global_discount = 0.0;

        foreach ($invoice_items as $invoice_item) {
            $value = 0.0;
            $value += $invoice_item->quantity * $invoice_item->unit_price;
            $total_liquid += $value;
            if ($invoice_item->discount > 0) {
                $commercial_discount += $value * $invoice_item->discount / 100;
                $value = $value - ($value * $invoice_item->discount / 100);
            }
            if ($invoice->global_discount > 0) {
                $global_discount += $value * $invoice->global_discount / 100;
                $value = $value - ($value * $invoice->global_discount / 100);
            }
            $total += $value;
            $vat += $value * $invoice_item->vat_type->percent / 100;
        }
        return array(
            "total_liquid" => $total_liquid,
            "total" => $total,
            "vat" => $vat,
            "commercial_discount" => $commercial_discount,
            "global_discount" => $global_discount
        );
    }

    private function calculate_vats($invoice, $invoice_items, $vat_types)
    {
        $vat_table_values = [];
        foreach ($vat_types as $vat_type) {
            $value = 0.0;
            $vat_value = 0.0;
            foreach ($invoice_items as $invoice_item) {
                if ($vat_type->id == $invoice_item->vat_type_id) {
                    $value = $invoice_item->quantity * $invoice_item->unit_price;
                    if ($invoice_item->discount > 0) {
                        $value = $value - ($value * $invoice_item->discount) / 100;
                    }
                    if ($invoice->global_discount > 0) {
                        $value = $value - ($value * $invoice->global_discount) / 100;
                    }
                    $vat_value += $value * $invoice_item->vat_type->percent / 100;
                }
            }

            if ($value > 0) {
                array_push(
                    $vat_table_values,
                    [
                        'tax' => $vat_type->percent . ' %',
                        'value' => $value,
                        'vat_value' => $vat_value,
                        'code' => $vat_type->code
                    ]
                );
            }
        }
        return $vat_table_values;
    }

    public function render(
        $invoice,
        $invoice_items,
        $global_settings,
        $company_information,
        $vat_types
    ) {
        $pages = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>Document</title>
            <style>
                body {
                    /*height: 842px;*/
                    /*width: 595px;*/
                    /* to centre page on screen*/
                    margin-left: auto;
                    margin-right: auto;
                }
                * {
                    font: Calibri, sans-serif !important;
                    font-size: 9px;
                }
                .container{
                    padding: 5px 100px 5px 100px; 
                }
                b.half{
                    font-weight: 550;
                }
                .column {
                    float: left;
                    width: 50%;
                }                  
                .row:after {
                    content: "";
                    display: table;
                    clear: both;
                }
                .column-30 {
                    float: left;
                    width: 30%;
                }
                .column-70 {
                    float: left;
                    width: 70%;
                }
                div.bordered {
                    border-style: solid;
                    border-width: 2px;
                }
                .watermark {
                    opacity: 0.5;
                    color: red;
                    position: absolute;
                    font-size: 12px;
                    display: block;
                    transform: rotate(45deg);
                    -webkit-transform: rotate(45deg);
                    -moz-transform: rotate(45deg);
                }
                .qrcode {
                    position: absolute;
                    top: 20px;
                    right: 20px;
                }
                .right{
                    float:right;
                }

                .left{
                    float:left;
                }
                .new-page {
                    page-break-before: always;
                }

            </style>
        </head>
        <body>';
        $html = '';

        if ($invoice->is_canceled == true) {
            for($i=0; $i<=100; $i += 5)
            {
                for($j=0; $j<=100; $j += 10)
                {
                    $html = $html . '<div class="watermark" style="top:' . $i . '%; left:' . $j . '%">
                    ' . trans('template.invoice_canceled') . '
                    </div>';
                }
            }
        }

        $html = $html . '<div class="container" style="position: relative;">';
        $html = $html . $this->render_qr(
            $invoice,
            $invoice_items,
            $global_settings,
            $company_information,
            $vat_types
        );

        $html = $html . '<img style="height:50px;" src="' . public_path() . $global_settings->site_logo . '"/>
        
                <div class="row">
                    <div class="column" style="font-size: 18px;">
                        <p><b class="half">' . $company_information->name . '</b></p>
                        <p>CRC: ' . $company_information->crc . ', № ' . $company_information->crc_number . ' </p>
                        <p>' . trans('template.Company_capital') . ' ' . $company_information->capital . '</p>
                        <p><b class="half">' . trans('template.Company_tax_number') . ' ' . $company_information->tax_number . '</b></p>
                        <p>' . $company_information->address . '</p>
                        <p>' . $company_information->city . '</p>
                        <p>' . $company_information->postal_code . ', ' . $company_information->city . '</p>
                        <p>' . trans('calendar.Phone') . ' ' . $company_information->phone . '</p>
                        <p>' . $company_information->email . '</p>
                        <p>' . $company_information->web_site . '</p>
                    </div>';
        if ($invoice->is_final_consumer != true) {
            $html = $html . '
                            <div class="column" style="font-size: 20px;">
                                <p>' . trans('template.Dear_customer_invoice') . '</p>
                                <p>' . $invoice->name . '</p>
                                <p>' . $invoice->address . '</p>
                                <p>' . $invoice->code . ' ' . $invoice->city . '</p>
                            </div>';
        } else {
            $html = $html . '
                            <div class="column" style="font-size: 20px;">
                                <p>' . trans('template.final_consumer') . '</p>
                            </div>';
        }
        $html = $html . '</div>

                <span class="right"><b class="half">Original</b></span><span class="left"><b class="half">' . $invoice->invoice_document_type->name . ' ' . $invoice->invoice_no . '</b></span>';

        if ($invoice->parent_invoice_id > 0) {
            $html = $html . '        
                    <span style="margin-left: 20px;">Doc. Referente :  ' . $invoice->parent->invoice_no . '</span>';
        }
        if ($invoice->service_id > 0) {
            $html = $html . '        
                    <span style="margin-left: 20px;">Doc. Referente :  ' . $invoice->service->generate_estimate_number() . '</span>';
        }

        $html = $html . '';

        $html = $html . '<br /><hr style="border-width: 3px solid black;" />
        
                <table style="width: 100%">
                    <tr>
                        <td valign="top">
                            <p><b class="half">' . trans('template.Company_tax_number') . '</b></p>
                            <p>' . $invoice->nif . '</p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('template.date_of_emission') . '</b></p>
                            <p>' . Carbon::parse($invoice->invoice_date)->toDateString() . '</p>
                        </td>';
        if ($invoice->invoice_document_type->code != 'NC' && $invoice->is_valued != false) {
            $html = $html . '<td valign="top">
                            <p><b class="half">' . trans('template.Maturity') . '</b></p>
                            <p>' . Carbon::parse($invoice->maturity)->toDateString() . '</p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('template.Conditions_of_payment') . '</b></p>
                            <p>' . $invoice->payment_condition->name . '</p>
                        </td>';
        }
        if ($invoice->is_valued != false) {
            $html = $html . '
                        </tr>
                        <tr>
                            <td valign="top">
                                <p><b class="half">' . trans('template.Currency') . '</b></p>
                                <p>' . $invoice->currency . '</p>
                            </td>
                            <td valign="top">
                                <p><b class="half">' . trans('template.Exchange') . '</b></p>
                                <p>' . $invoice->exchange . '</p>
                            </td>';
            if ($invoice->is_paid == true) {
                $html = $html . '
                                <td valign="top" colspan="2">
                                    <p><b class="half">' . trans('template.movement_type') . '</b></p>
                                    <p>' . $invoice->movement_type->name . ' - ' . $invoice->movement_type->description . '</p>
                                </td>';
            }
        }

        $html = $html . '               
                    </tr>
                </table>
                <hr style="border-width: 2px;" />
                <table style="width: 100%">
                    <tr>
                        <td valign="top">
                            <p><b class="half">' . trans('estimate.Artigo') . '</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('template.code') . '</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('template.Description') . '</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('estimate.Quantidade') . '</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('estimate.Units') . '</b></p>
                        </td>';
        if ($invoice->is_valued != false) {
            $html = $html . '
                        <td valign="top">
                            <p><b class="half">' . trans('template.pr_initario') . '</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('template.desc') . '</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('estimate.IVA') . '</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">' . trans('estimate.Valor') . '</b></p>
                        </td>
                    </tr>';
        }

        $total_liquid = 0.0;
        $total = 0.0;
        $vat = 0.0;
        $commercial_discount = 0.0;
        $global_discount = 0.0;

        foreach ($invoice_items as $invoice_item) {
            $value = 0.0;
            $value += $invoice_item->quantity * $invoice_item->unit_price;
            $total_liquid += $value;
            if ($invoice_item->discount > 0) {
                $commercial_discount += $value * $invoice_item->discount / 100;
                $value = $value - ($value * $invoice_item->discount / 100);
            }
            if ($invoice->global_discount > 0) {
                $global_discount += $value * $invoice->global_discount / 100;
                $value = $value - ($value * $invoice->global_discount / 100);
            }
            $total += $value;
            $vat += $value * $invoice_item->vat_type->percent / 100;
            $html = $html . '
                    <tr>
                        <td valign="top">
                            <p>' . $invoice_item->product->name . '</p>
                        </td>
                        <td valign="top">
                            <p>' . $invoice_item->product->code . '</p>
                        </td>
                        <td valign="top">
                            <p>' . $invoice_item->description . '</p>
                        </td>
                        <td valign="top">
                            <p>' . $invoice_item->quantity . '</p>
                        </td>
                        <td valign="top">
                            <p>' . $invoice_item->unit . '</p>
                        </td>';
                        
                            if ($invoice->is_valued != false) {
                                $html = $html . '<td valign="top">
                                <p>';

            $html = $html . number_format($invoice_item->unit_price, 2);                      

            $html = $html . '</p>
                        </td>
                        <td valign="top">
                            <p>';
            $html = $html . $invoice_item->discount . '%';                        

            $html = $html . '</p>
                        </td>
                        <td valign="top">
                            <p>' . $invoice_item->vat_type->percent . '%</p>
                        </td>
                        <td valign="top">
                            <p>';

            
                $html = $html . number_format($value, 2);
            }

            $html = $html . '</p>
                        </td>';
        }
        $html = $html . ' </tr>
                        ';


        $vat_table_values = $this->calculate_vats($invoice, $invoice_items, $vat_types);

        $html = $html . '
                </table>          
                <br>';


        if (in_array($invoice->invoice_document_type->code, ['GT', 'GR', 'NE', 'FP'])) {
            $html = $html . '<p>Este documento não serve de fatura</p>';
        }

        if (in_array($invoice->invoice_document_type->code, ['GT', 'GR'])) {
            $html = $html . '<div class="row">';

            $html = $html . '<div class="column">';
            $html = $html . '<h2>' . trans('template.loading_location') . '</h2>';
            $html = $html . '<p>' . trans('calendar.Address') . ' :  ' . $invoice->loading_address . '</p>';
            $html = $html . '<p>' . trans('dashboard.Region') . ' :  ' . $invoice->loading_city . '</p>';
            $html = $html . '<p>' . trans('estimate.Postal_code') . ' :  ' . $invoice->loading_postal_code . '</p>';
            $html = $html . '<p>' . trans('template.Country') . ' :  ' . $invoice->loading_country . '</p>';
            $html = $html . '<p>' . trans('estimate.Data') . ' :  ' . $invoice->loading_date . '</p>';
            $html = $html . '</div>';


            $html = $html . '<div class="column">';
            $html = $html . '<h2>' . trans('template.dicharge_location') . '</h2>';
            $html = $html . '<p>' . trans('calendar.Address') . ' :  ' . $invoice->discharge_address . '</p>';
            $html = $html . '<p>' . trans('dashboard.Region') . ' :  ' . $invoice->discharge_city . '</p>';
            $html = $html . '<p>' . trans('estimate.Postal_code') . ' :  ' . $invoice->discharge_postal_code . '</p>';
            $html = $html . '<p>' . trans('template.Country') . ' :  ' . $invoice->discharge_country . '</p>';
            $html = $html . '<p>' . trans('template.dicharge_registration') . ' :  ' . $invoice->discharge_registration . '</p>';
            $html = $html . '<p>' . trans('estimate.Data') . ' :  ' . $invoice->discharge_date . '</p>';
            $html = $html . '</div>';

            $html = $html . '</div>';
        }

        if ($invoice->parent_invoice_id > 0 && ($invoice->invoice_document_type->code == 'NC' || $invoice->invoice_document_type->code == 'ND')) {
            if ($invoice->invoice_date != $invoice->parent->invoice_date) {
                $html = $html . '
                <p> ' . trans('template.products_and_services_were_earlier') . ' ' . $invoice->parent->invoice_date . '</p>';
            }
        }


        $html = $html . '<br>
                <p> ' . $this->get_signature_characters($invoice) . '-Processado por Programa Certificado n.º 9999/AT / ' . $invoice->invoice_no . ' / © ODIGA LDA DIGITAL INTELLIGENCE AND GLOBAL ANALYTICS </p>';

        if ($invoice->is_valued != false){

        $html = $html . '<hr style="border-width: 2px;" />
                    <div class="row">
                        <div class="column-70">
                            <p><b class="half">' . trans('template.Summary_table_of_iva') . '</b></p>
                            <table style="width: 100%">
                                <tr>
                                    <td>
                                        <p><b class="half">' . trans('template.tax') . '</b></p>
                                    </td>
                                    <td>
                                        <p><b class="half">' . trans('template.incidence') . '</b></p>
                                    </td>
                                    <td>
                                        <p><b class="half">' . trans('template.total_vat') . '</b></p>
                                    </td>
                                    <td>
                                        <p><b class="half">' . trans('template.reason_for_exemption') . '</b></p>
                                    </td>
                                </tr>';

        foreach ($vat_table_values as $vv) {
            $html = $html . '
                                <tr>
                                    <td>
                                        <p>' . $vv['tax'] . '</p>
                                    </td>
                                    <td>
                                        <p>' . number_format((float)$vv['value'], 2) . '</p>
                                    </td>
                                    <td>
                                        <p>' . number_format((float)$vv['vat_value'], 2) . '</p>
                                    </td>';

            if ($vv['vat_value'] == 0) {
                $html = $html . '
                                        <td>
                                            <p>' . $invoice->vat_exemption_reason->name . '</p>
                                        </td>
                                        ';
            }

            $html = $html . '
                                </tr>
                                    ';
        }

        $html = $html . '
    
                            </table>';

        if (!in_array($invoice->invoice_document_type->code, ['GT', 'GR', 'NE', 'NC'])) {
            $html = $html . '
                                <div class="bordered" style="width: 60%">
                                    <p style="margin-left: 5px;">' . trans('template.for_payment_via_bank_transfer') . ':</p>
                                    <p style="margin-left: 5px;">' . trans('template.Company_bank') . ': ' . $company_information->bank . '</p>
                                    <p style="margin-left: 5px;">IBAN: ' . $company_information->iban . ' * BIC SWIFT: ' . $company_information->swift . '</p>
                                </div>
                                ';
        }

        $html = $html . '
                            <br>

                            <div>' . $global_settings->invoice_notes .
            '</div>
            
                        </div>
                        <div class="column-30">
                            <table style="width: 100%">
                                <tr>
                                    <td valign="left">
                                        ' . trans('template.merchandise') . '/' . trans('template.ServiceSettings') . '
                                    </td>
                                    <td valign="right">
                                        ' . number_format((float)$total_liquid, 2) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="left">
                                        ' . trans('template.commercial_discounts') . '
                                    </td>
                                    <td valign="right">
                                        ' . number_format((float) $commercial_discount, 2) . '
                                    </td>
                                </tr>

                                <tr>
                                    <td valign="left">
                                        ' . trans('template.global_discount') . '
                                    </td>
                                    <td valign="right">
                                        ' . number_format((float) $global_discount, 2) . '
                                    </td>
                                </tr>
                                 <tr>
                                    <td valign="left">
                                        ' . trans('client.vat') . '
                                    </td>
                                    <td valign="right">
                                        ' . number_format((float)$vat, 2) . '
                                    </td>
                                </tr>
                            </table>
                            <br>
                            <br>
                            <hr style="border-width: 1px;">
                            <table style="width: 100%">
                                <tr>
                                    <td valign="left">
                                        <h2>Total(EUR)</h2>
                                    </td>
                                    <td valign="right">
                                        <h2>' . number_format($total + $vat - $invoice->desc_cli - $invoice->desc_fin + $invoice->postage + $invoice->other_services + $invoice->advances, 2) . '</h2>
                                    </td>
                                </tr>';

        if ($invoice->currency != 'EUR') {
            $html = $html . '<tr>
                                    <td valign="left">
                                        <h2>Total(' . $invoice->currency . ')</h2>
                                    </td>
                                    <td valign="right">
                                        <h2>' . number_format(($total + $vat - $invoice->desc_cli - $invoice->desc_fin + $invoice->postage + $invoice->other_services + $invoice->advances) * $invoice->exchange, 2) . '</h2>
                                    </td>
                                </tr>';
        }}

        $html = $html . '
                            </table>
                        </div>
                    </div>        
            
                </div>';
        

        $html_duplicate = $html;
        $html_duplicate = str_replace('<b class="half">Original</b>', '<b class="half">Duplicado</b>', $html_duplicate);
        $pages = $pages .  $html . '<div class="new-page"></div>' . $html_duplicate;
        $pages = $pages . '
        </body>
        </html>      
        ';



        return $pages;
    }
}
