<?php

namespace Rkesa\Calendar\Http\Helpers;

use App\GlobalSettings;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Response;
use Exception;
use Log;
use DateTime;

class ChecklistPDFCreator {

    public function render_pdf($data, $service, $checklist, $filled = null){
        $footer = '<!DOCTYPE html>
                    <head>
                        <meta charset="UTF-8">
                        <style>
                            @font-face {
                                font-family: Arialn; /* Имя шрифта */
                                src: url('.public_path('/vendor/estimate/arialn.ttf').') /* Путь к файлу со шрифтом */
                            }
                            *{
                                font-family: Arialn;
                                font-size: 16px;
                            }
                        </style>
                    </head>
                    <body style="height:30px;overflow:hidden;margin:0;padding:0;margin-right: 60px;">
                        <div style="text-align: right;">'.$checklist->footer.'</div>
                    </body></html>';

        $header = '<!DOCTYPE html>
                    <head>
                        <meta charset="UTF-8">
                    </head>
                    <body style="height:80px;overflow:hidden;margin:0 60px;;padding:0;">
                        <table style="border-bottom: 1px solid black;width: 100%;">
                            <tr>
                                <td><img style="margin-top:15px;display:block;margin-right:35px;max-width:260px;max-height:40px;" src="'.public_path().GlobalSettings::first()->site_logo.'"/></td>
                                <td style="font-weight: bold;text-align: right;font-size: 20px;text-transform: uppercase;">'.$checklist->name.'</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="text-align: right;">
                                    <div style="margin-left: auto; text-align: right; border: 1px solid black; padding: 2px; height:13px; width:100px;">
                                        '.($service ? ($service->estimate_number) : '').'
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </body>
                    </html>';

        $pdf = new Pdf(array(
            'no-outline',         // Make Chrome not complain
            'margin-right'  => 0,
            'margin-left'   => 0,
            'header-html' => $header,
            'header-spacing' => 2,
            'footer-html' => $footer,
            'encoding' => 'UTF-8',
        ));

        $pdf->addPage($this->render($data, $service, $checklist, $filled));

        $result = $pdf->toString();
        if ($result === false) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        return $result;
    }

    public function render_html($data, $service, $checklist, $filled = null){
        return $this->render($data, $service, $checklist, $filled);
    }

    private function get_answer($entity, $filled)
    {
        foreach($filled->checklist_filled_entities as $filled_entity){
            if ($filled_entity->checklist_entity_id == $entity->id){
                return $filled_entity;
            }
        }
    }

    public function render($data, $service, $checklist, $filled){
        if ($data){
            $contact = $data->client_contact;
            $responsible_name = $data->user->name;
        }else{
            if ($service){
                $contact = $service->client_contact;
                if ($service->responsible_user) {
                    $responsible_name = $service->responsible_user->name;
                }else{
                    $responsible_name = '';
                }
            }else{
                $contact = null;
                $responsible_name = '';
            }
        }
        $full_name = $contact ? $contact->name.' '.$contact->surname : '';

        $phones = [];
        if ($contact) {
            foreach ($contact->client_contact_phones as $phone) {
                array_push($phones, $phone->phone_number);
            }
        }
        $phones = join(', ', $phones);

        $emails = [];
        if ($contact) {
            foreach ($contact->client_contact_emails as $email) {
                array_push($emails, $email->email);
            }
        }
        $emails = join(', ', $emails);

        $questions = '';
        foreach($checklist->checklist_entities as $question) {
            $questions .= '<tr>
                <td style="background-color: '.$question->color.'; text-transform: uppercase;">'.$question->name.'</td>
                <td>'.($filled ? self::get_answer($question, $filled)->text : '').'</td>
            </tr>';
        }

        $works_and_areas = [];
        foreach($checklist->checklist_works as $index=>$work)
        {
            $works_and_areas[$index] = 
            '<tr>
                <td style="text-transform: uppercase; height: 35px;">'.$work->text.'</td>
                <td></td>
                <td></td>';
        }

        
        foreach($checklist->checklist_areas as $index=>$area)
        {
            if (array_key_exists($index, $works_and_areas))
            {
                $works_and_areas[$index] .= '
                <td style="text-transform: uppercase;">
                    '.$area->text.'
                </td>
                <td></td>';
            }
            else
            {
                $works_and_areas[$index] = '
                <td></td>
                <td></td>
                <td></td>
                <td style="text-transform: uppercase;">
                    '.$area->text.'
                </td>
                <td></td>
                ';
            }
        }

        foreach($works_and_areas as $wa)
        {
            $wa .= '</tr>';
        }

        $second_page = '<div>
                            <table class="emptytable" style="text-transform: uppercase;">
                                <tr>
                                    <td>#</td>
                                    <td style="width: 30%;">'.trans('calendar::calendar.Designation_of_work').'</td>
                                    <td>'.trans('calendar::calendar.Un').'</td>
                                    <td>'.trans('calendar::calendar.Length_or_perimeter').'</td>
                                    <td>'.trans('calendar::calendar.Width').'</td>
                                    <td>'.trans('calendar::calendar.Height').'</td>
                                    <td>'.trans('calendar::calendar.Total_amounts').'</td>
                                </tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                                <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                            </table>
                        </div>';

        $html='<!DOCTYPE html>
				<html>
					<head>
						<meta charset="UTF-8">
						<style>
						    @font-face {
                                font-family: Arialn; /* Имя шрифта */
                                src: url('.public_path('/vendor/estimate/arialn.ttf'). ') /* Путь к файлу со шрифтом */
                            }
                            *{
                                font-family: Arialn;
                                font-size: 16px;
                            }
                            html, body{
                                margin:0;
                                padding:0;
                            }
                            table{
                                width: 100%;
                                border-collapse: collapse;
                            }
                            .vertical-text{
                                text-align: center;
                                vertical-align: middle;
                                width: 20px;
                                white-space: nowrap;
                                -webkit-transform: rotate(-90deg); 
                                -moz-transform: rotate(-90deg);  
                                color: red;
                                text-decoration: underline;
                                font-weight: bold;
                            }
                            td{
                                border: 1px solid black;
                                padding: 5px;
                            }
                            .noborder{
                                margin: 20px 0;
                                font-size: 16px;
                            }
                            .noborder td{
                                border: none;
                                padding: 5px 0 0;
                            }
                            .blue-back{
                                background-color: #07eeeb;
                            }
                            .yellow-back{
                                background-color: #eee60f;
                            }
                            .emptytable{
                                text-align: center;
                            }
                            .emptytable td{
                                padding: 25px;
                            }
						</style>
					</head>
					<body style="margin: 0 60px;">
						<div>
							<div>
							    <table class="noborder">
							        <tr>
							            <td style="width: 25%;">'.trans('calendar::calendar.Date').': '.($data ? (new \DateTime($data->start))->format('d.m.Y') : '').'</td>
                                        <td style="width: 25%;">'.trans('calendar::calendar.Hours').': '.($data ? (new \DateTime($data->start))->format('H:i') : '').'</td>
                                        <td style="width: 25%;">'.trans('calendar::calendar.Proposta').': '.($service ? ($service->estimate_number) : '').' </td>
                                        <td style="width: 25%;">'.trans('calendar::calendar.Responsible_user').': '.$responsible_name.'</td>							            
                                    </tr>
                                    <tr>
                                        <td colspan="2">'.trans('calendar::calendar.Client_name').': '.$full_name.'</td>
                                        <td colspan="2">'.trans('calendar::calendar.Contact').': </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align:top" colspan="2" rowspan="3">'.trans('calendar::calendar.Condominio').': </td>
                                        <td colspan="2">Email: '.$emails.'</td>                                    
                                    </tr>
                                    <tr>                                                                            
                                        <td colspan="2">'.trans('calendar::calendar.Phone').': '.$phones.'</td>
                                    </tr>
                                    <tr>                                                                            
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">'.trans('calendar::calendar.Address').': '.($service ? $service->address : '').'</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">'.trans('calendar::calendar.Service').': '.($service ? $service->name : '').'</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">'.trans('calendar::calendar.Notes').': '.($data ? $data->description : '').'</td>
                                    </tr>
                                </table>
                                <br>
                                <br>
                                <p>'.trans('calendar::calendar.PrazoPara').':</p>
                                <p>'.trans('calendar::calendar.DataPrevista').':</p>
                                <br>
                                <br>
								<table style="width:500px;">
									<tbody>
									    <tr>
                                            <td style="font-weight: bold;background-color: '.GlobalSettings::first()->color3.';width:80%;text-align: center;color:#ffffff;">'.trans('calendar::calendar.CustomerQuestions').'</td>
                                            <td style="font-weight: bold;background-color: '.GlobalSettings::first()->color3.';width:20%;text-align: center;color:#ffffff;">'.trans('calendar::calendar.AnswerYesNo').'</td>
                                        </tr>'.$questions.
                                    '</tbody>
								</table>
                            </div>
                            <div style="border: 1px solid black;height: 130px;padding: 5px;">'.trans('calendar::calendar.OtherQuestions').':</div>
                            <div style="border: 1px solid black;height: 130px;padding: 5px;page-break-after: always;">'.trans('calendar::calendar.Notes').':</div>
                            
                            <h1 align="center">'.trans('calendar::calendar.BudgetWork').'</h1>
                            <table style="height: 700px;">
                                <tbody>
                                    <tr>
                                        <td style="font-weight: bold;width:20%;text-align: center;">'.trans('calendar::calendar.WorkStages').'</td>
                                        <td style="font-weight: bold;width:5%;text-align: center;color:#ffffff;"></td>
                                        <td style="font-weight: bold;width:55%;text-align: center;color:#ffffff;"></td>
                                        <td style="font-weight: bold;width:15%;text-align: center;color:#ffffff;"></td>
                                        <td style="font-weight: bold;width:5%;text-align: center;color:#ffffff;"></td>
                                    </tr>

                                    '.implode (" ", $works_and_areas).'
                                    <tr style="height:40px;">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr style="height:40px;">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div style="border: 1px solid black;height: 130px;padding: 5px;">'.trans('calendar::calendar.Notes').':</div>
                            <div style="overflow: hidden;">
                                <p style="float: left; padding-left:50px;">'.trans('calendar::calendar.ResponsibleSignature').'</p>
                                <p style="float: right; padding-right:50px;">'.trans('calendar::calendar.ClientSignature').'</p>
                            </div>
                            <div style="overflow: hidden;">
                                <p style="float: left; padding-left:50px;">__________________________</p>
                                <p style="float: right; padding-right:50px;">__________________________</p>
                            </div>
						</div>
					</body>
				</html>';

        return $html;
    }

}
