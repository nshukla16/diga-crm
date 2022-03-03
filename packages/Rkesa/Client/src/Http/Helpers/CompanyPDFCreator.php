<?php

namespace Rkesa\Client\Http\Helpers;

use App\GlobalSettings;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Response;
use Exception;

class CompanyPDFCreator {

    public function render_pdf($data = null){
        $footer = '<!DOCTYPE html><head>
                            <style>
                            @font-face {
                                font-family: Arialn; /* Имя шрифта */
                                src: url('.public_path('/vendor/estimate/arialn.ttf').') /* Путь к файлу со шрифтом */
                            }
                            *{
                                font-family: Arialn;
                                font-size: 8px;
                            }</style>
                            </head>
                            <body style="text-align:right;"><div style="height: 30px;"></div></body></html>';

        $header = '<!DOCTYPE html>
                    <head>
                        <meta charset="UTF-8">
                    </head>
                    <body style="height:80px;overflow:hidden;margin:0 60px;;padding:0;">
                        <table style="border-bottom: 1px solid black;width: 100%;">
                            <tr>
                                <td><img style="margin-top:15px;display:block;margin-right:35px;max-width:260px;max-height:60px;" src="'.public_path().GlobalSettings::first()->site_logo.'"/></td>
                                <td style="font-weight: bold;text-align: right;font-size: 24px;text-transform: uppercase;">'.trans('client::client.Company_card').'</td>
                            </tr>
                        </table>
                    </body>
                    </html>';

        $pdf = new Pdf(array(
            'no-outline',         // Make Chrome not complain
            'margin-right'  => 0,
            'margin-left'   => 0,
            'header-spacing' => 2,
            'header-html' => $header,
            'footer-html' => $footer,
        ));

        $pdf->addPage($this->render($data));

        $result = $pdf->toString();
        if ($result === false) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        return $result;
    }

    public function render_html($data = null){
        return Response($this->render($data));
    }

    public function render($data = null){
        $add_fields = '';
        foreach($data->attributes_calculated as $attr){
            $add_fields .= '<tr>
                                <td class="light-colored">'.$attr['name'].':</td>
                                <td>' . $attr['value_calculated'] . '</td>
                            </tr>';
        }

        $html = '<!DOCTYPE html>
				<html>
					<head>
						<meta charset="UTF-8">
						<style>
						    @font-face {
                                font-family: Arialn; /* Имя шрифта */
                                src: url('.public_path('/vendor/estimate/arialn.ttf').') /* Путь к файлу со шрифтом */
                            }
                            *{
                                font-family: Arialn;
                                font-size: 11px;
                            }
                            html, body{
                                margin:0;
                                padding:0;
                            }
                            table { page-break-inside:auto; margin-top: 30px; border: 1px solid black; border-collapse: collapse; }
                            .centered { text-align: center; }
                            tr { page-break-inside:avoid; page-break-after:auto }
                            td { border: 1px solid black; height: 18px; padding: 5px;}
                            thead { display:table-header-group; }
                            tfoot { display:table-footer-group }
                            .heavy-colored {
                                background-color: '.GlobalSettings::first()->color3.';
                                color: white;
                                font-size: 16px;
                            }
                            .light-colored {
                                background-color: '.GlobalSettings::first()->color4.';
                            }
                        </style>
                    </head>
                    <body style="margin: 0px 60px 31px;">
                        <table style="width:100%;">
                            <tbody>
                                <tr>
                                    <td class="light-colored" style="width: 150px;">'.trans('client::client.Client_name').':</td>
                                    <td>' . $data->name . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Address_legal').':</td>
                                    <td>' . $data->address_legal . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Address_mailing').':</td>
                                    <td>' . $data->address_mailing . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.NIF').':</td>
                                    <td>' . $data->nif . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Checking_account').':</td>
                                    <td>' . $data->checking_account . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Correspondent_account').':</td>
                                    <td>' . $data->correspondent_account . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Bic').':</td>
                                    <td>' . $data->bic . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Phones').':</td>
                                    <td>' . $data->phone . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Site').':</td>
                                    <td>' . $data->site . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Email').':</td>
                                    <td>' . $data->email . '</td>
                                </tr>
                                    '.$add_fields.'
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Client_group').':</td>
                                    <td>' . $data->client_group . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Referrer').':</td>
                                    <td>' . ($data->client_referrer ? $data->client_referrer->title : '') . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Referrer_note').':</td>
                                    <td>' . $data->referrer_note . '</td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('client::client.Note').':</td>
                                    <td>' . $data->note . '</td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                </html>';

        return $html;
    }

}
