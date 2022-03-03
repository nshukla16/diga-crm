<?php

namespace Rkesa\Hr\Http\Helpers;

use App\GlobalSettings;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Response;
use Exception;

class HrPDFCreator {

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
                            <body style="text-align:right;"><div style="height: 30px;">
                            '.GlobalSettings::first()->hr_bottom_text.'
                            </div></body></html>';


        $pdf = new Pdf(array(
            'no-outline',         // Make Chrome not complain
            'margin-top' => 2,
            'header-spacing' => 2,
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
        $photo = '';
        if ($data !== null){
            $exp_before = $work_before = '';
            foreach($data->user_experiences as $exp){
                if ($exp->type == 1){
                    $work_before .= '<tr>
                                        <td>'.$exp->begin.'</td>
                                        <td>'.$exp->end.'</td>
                                        <td>'.$exp->description.'</td>
                                        <td>'.$exp->place.'</td>
                                    </tr>';
                }elseif ($exp->type == 2){
                    $exp_before  .= '<tr>
                                        <td>'.$exp->begin.'</td>
                                        <td>'.$exp->end.'</td>
                                        <td>'.$exp->description.'</td>
                                        <td>'.$exp->place.'</td>
                                    </tr>';
                }
            }
            $photo = '<div style="float: right;margin-top: 15px;margin-bottom:15px;"><img style="height: 60px;" src="'.public_path($data->photo).'"/></div>';
        }else{
            $exp_before = $work_before = '<tr><td></td><td></td><td></td><td></td></tr>
                                      <tr><td></td><td></td><td></td><td></td></tr>
                                      <tr><td></td><td></td><td></td><td></td></tr>
                                      <tr><td></td><td></td><td></td><td></td></tr>';
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
                    <body>
                        <div style="text-align: right; font-weight: bold; font-size: 16px;padding-top: 30px;">'.trans('hr::hr.COLLABORATORS_DETAILS').'</div>
                        <div style="float:left;margin-bottom: 20px;">
                            <img style="margin-top:15px;margin-bottom:15px;display:block;max-width:260px;max-height:60px;" src="'.public_path().GlobalSettings::first()->site_logo.'"/>
                        </div>
                        '.$photo.'
                        <table style="width:100%;">
                            <tbody>
                                <tr>
                                    <td colspan="4" class="heavy-colored centered">'.trans('hr::hr.Personal_information').'</td>
                                </tr>
                                <tr>
                                    <td class="light-colored" style="width: 90px;">'.trans('hr::hr.Name').':</td>
                                    <td colspan="3">
                                        ' . ($data !== null ? $data->name : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Address').':</td>
                                    <td colspan="3">
                                        ' . ($data !== null ? $data->address : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Postal_Code').':</td>
                                    <td colspan="3">
                                        ' . ($data !== null ? $data->postal : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Nation').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->nation : ' ' ) . '
                                    </td>
                                    <td class="light-colored" style="width: 90px;">'.trans('hr::hr.Birthday_date').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->birthday : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Identical_Number').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->identical_number : ' ' ) . '
                                    </td>
                                    <td class="light-colored">'.trans('hr::hr.Tax_Number').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->tax_number : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Bank_Number').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->bank_number : ' ' ) . '
                                    </td>
                                    <td class="light-colored">'.trans('hr::hr.Education_experience').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->education : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Driver_Number').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->driver_number : ' ' ) . '
                                    </td>
                                    <td class="light-colored">'.trans('hr::hr.Languages').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->languages : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Home_Phone_Number').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->home_phone : ' ' ) . '
                                    </td>
                                    <td class="light-colored">E-mail:</td>
                                    <td>
                                        ' . ($data !== null ? $data->additional_email : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Cell_Phone_Number').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->cell_phone : ' ' ) . '
                                    </td>
                                    <td class="light-colored">'.trans('hr::hr.Dependencies').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->dependencies : ' ' ) . '
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="width:50%;">
                            <tbody>
                                <tr>
                                    <td colspan="2" class="heavy-colored centered">'.trans('hr::hr.Emergency_contact').'</td>
                                </tr>
                                <tr>
                                    <td class="light-colored" style="width: 90px;">'.trans('hr::hr.Name').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->emergency_name : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Contact').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->emergency_contact : ' ' ) . '
                                    </td>
                                </tr>
                                <tr>
                                    <td class="light-colored">'.trans('hr::hr.Relation').':</td>
                                    <td>
                                        ' . ($data !== null ? $data->emergency_relation : ' ' ) . '
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="width:100%;">
                            <tbody>
                                <tr>
                                    <td colspan="4" class="heavy-colored centered">'.__('hr::hr.Work_experience_before_company', ['company' => GlobalSettings::first()->site_name]).'</td>
                                </tr>
                                <tr>
                                    <td class="light-colored  centered" style="width: 25%;">'.trans('hr::hr.begin').'</td>
                                    <td class="light-colored  centered" style="width: 25%;">'.trans('hr::hr.End').'</td>
                                    <td class="light-colored  centered" style="width: 25%;">'.trans('hr::hr.Specialty').'</td>
                                    <td class="light-colored  centered" style="width: 25%;">'.trans('hr::hr.Company').'</td>
                                </tr>
                                ' . $work_before . '
                            </tbody>
                        </table>
                        <table style="width:100%;">
                            <tbody>
                                <tr>
                                    <td colspan="4" class="heavy-colored centered">'.__('hr::hr.Education_experience_before_company', ['company' => GlobalSettings::first()->site_name]).'</td>
                                </tr>
                                <tr>
                                    <td class="light-colored  centered" style="width: 25%;">'.trans('hr::hr.begin').'</td>
                                    <td class="light-colored  centered" style="width: 25%;">'.trans('hr::hr.End').'</td>
                                    <td class="light-colored  centered" style="width: 25%;">'.trans('hr::hr.Tema').'</td>
                                    <td class="light-colored  centered" style="width: 25%;">'.trans('hr::hr.Place').'</td>
                                </tr>
                                ' . $exp_before . '
                            </tbody>
                        </table>
                    </body>
                </html>';

        return $html;
    }

}
