<?php

namespace Rkesa\Estimate\Http\Helpers;

use App\GlobalSettings;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Response;
use Exception;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Log;

class PlanningPDFCreator {

    public function render_pdf($data){
        $footer = '<!DOCTYPE html><head>
                            <meta charset="utf-8">
                            <script>
                            function subst() {
                              var vars={};
                              var x=document.location.search.substring(1).split(\'&\');
                              for (var i in x) {var z=x[i].split(\'=\',2);vars[z[0]] = unescape(z[1]);}
                              var x=[\'frompage\',\'topage\',\'page\',\'webpage\',\'section\',\'subsection\',\'subsubsection\'];
                              for (var i in x) {
                                var y = document.getElementsByClassName(x[i]);
                                for (var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];
                              }
                            }
                            </script>
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
                            <body style="height:50px;overflow:hidden;margin:0;padding:0;" onload="subst()">
                                '.GlobalSettings::first()->estimate_bottom_text.'
                            </body></html>';

        $header = '<!DOCTYPE html><body style="height:73px;overflow:hidden;margin:0;padding:0;"><img style="margin-top:15px;display:block;float:right;margin-right:35px;max-width:260px;max-height:60px;" src="'.public_path().GlobalSettings::first()->site_logo.'"/></body></html>';

        $gs = GlobalSettings::first();

        $pdf = new Pdf(array(
            'no-outline',         // Make Chrome not complain
            'margin-right'  => 0,
            'margin-left'   => 0,
            'header-html' => $header,
            'header-spacing' => 2,
            'footer-html' => $footer,
            'encoding' => 'UTF-8',
            'orientation' => $gs->planning_orientation == 1 ? 'landscape' : 'portrait'
        ));

        $pdf->addPage($this->render($data));

        $result = $pdf->toString();
        if ($result === false) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        return $result;
    }

    public function render_html($data){
        return $this->render($data);
    }

    public function render($data){

        $table_top = '<tr>
					<td style = "width: 15%">'.trans('estimate::estimate.estimate_number').':</td>
					<td colspan="7">' . $data->get_estimate_number() .'</td>
				  </tr>
				  <tr>
					<td style = "width: 15%">'.trans('estimate::estimate.Assunto').':</td>
					<td colspan="7">' . htmlspecialchars($data->subject) . '</td>
				  </tr>
				  <tr>
					<td style = "width: 15%">'.trans('estimate::estimate.Obra_situada_em').':</td>
					<td colspan="7">' . htmlspecialchars($data->service->address) . '</td>
				  </tr>
				  <tr>
					<td style = "width: 15%">'.trans('estimate::estimate.Data').':</td>
					<td>____/____/______</td>
					<td style = "width: 15%;text-align:right;">'.trans('estimate::estimate.Work_start_date').':</td>
					<td>____/____/______</td>
					<td style = "width: 15%;text-align:right;">'.trans('estimate::estimate.Work_finish_date').':</td>
					<td>____/____/______</td>
					<td style = "width: 15%;text-align:right;">'.trans('estimate::estimate.Prazo').':</td>
					<td>___________ '.trans('estimate::estimate.days').'</td>
				  </tr>
				  <tr>
					<td style = "width: 15%">'.trans('estimate::estimate.Responsible').':</td>
					<td colspan="3">________________________________________________________________</td>
					<td style = "width: 15%;text-align:right;">'.trans('estimate::estimate.Created_by').':</td>
					<td colspan="3">________________________________________________________________</td>
				  </tr>
				  <tr>
					<td style = "width: 15%">'.trans('estimate::estimate.Color_references').':</td>
					<td>__________________;______________________;______________________</td>
					<td style = "width: 15%;text-align:right;">'.trans('estimate::estimate.Client_name').':</td>
					<td>_________________________________</td>
					<td style = "width: 15%;text-align:right;">'.trans('estimate::estimate.Client_contact').':</td>
					<td>_________________________________</td>
				  </tr>';

        $table_main = "";
        $lines = $data->lines_with_join();
        $lines = EstimateLine::array_to_tree_and_to_array($lines);
        $begin_of_estimate = true;
        foreach ($lines as $line) {
            switch ($line['lineable_type']) {
                case '\App\EstimateLineSeparator':
                    $table_main .= '<tr nobr="true"><td colspan="10" class = "align-mid bold background-gray-dark" style = "border: 0.1px solid black">' . htmlspecialchars($line['separator_name']) . '</td></tr>';
                    break;
                case '\App\EstimateLineCategory':
                    if (!$begin_of_estimate && $line['parent_id']==null){
                        $table_main .= '<tr nobr="true">
								<td colspan="10" style="height:15px;"></td>
							 </tr>';
                    } else {
                        $begin_of_estimate = false;
                    }

                    $table_main .= '<tr nobr="true">
								<td class = "align-mid bold background-gray-light tabletd">'.EstimateLine::gen_number($line['id'], $lines).'</td>
								<td colspan="9" class = "left bold background-gray-light tabletd" style = "width: 93%;">' . htmlspecialchars($line['category_name']) . '</td>
							 </tr>';
                    break;
                case '\App\EstimateLineData':
                    $un = EstimateUnit::convertUnit($line['data_measure']);
                    $maodeobra = EstimateLine::find($line['id'])->estimate_line_workers();
                    $count = $maodeobra->count();
                    $table_main .= '<tr nobr="true">
								<td rowspan="'.$count.'" class = "align-mid tabletd">'.EstimateLine::gen_number($line['id'], $lines).'</td>
								<td rowspan="'.$count.'" class = "tabletd">' . htmlspecialchars($line['data_description']) . '</td>
								<td rowspan="'.$count.'" class = "align-mid tabletd">' . $un . '</td>
								<td rowspan="'.$count.'" class = "align-mid tabletd">' . ($un=="" ? "" :  number_format($line['data_quantity'], 2, $dec_point = ",", $thousands_sep = ".") ) . '</td>
							    <td rowspan="'.$count.'" class="tabletd">' . htmlspecialchars($line['data_note']) . '</td>
							    <td class="tabletd">'.($count>0 ? $maodeobra->first()->user->name : '').'</td>
                                <td class="tabletd">'.($count>0 && $maodeobra->first()->hours != 0 ? $maodeobra->first()->hours : '').'</td>
                                <td class="tabletd"></td>
                                <td class="tabletd"></td>
                                <td class="tabletd"></td>
							  </tr>';
                    foreach($maodeobra->limit(10000)->offset(1)->get() as $mao){
                        $table_main .= '<tr nobr="true">
                                <td class="tabletd">'.$mao->user->name.'</td>
                                <td class="tabletd">'.($mao->hours != 0 ? $mao->hours : '').'</td>
                                <td class="tabletd"></td>
                                <td class="tabletd"></td>
                                <td class="tabletd"></td>
                            </tr>';
                    }
                    break;
                case '\App\EstimateLineFicha':
                    $un = EstimateUnit::convertUnit($line['ficha_measure']);
                    $maodeobra = EstimateLine::find($line['id'])->estimate_line_workers();
                    $count = $maodeobra->count();
                    $table_main .= '<tr nobr="true">
								<td rowspan="'.$count.'" class = "align-mid tabletd">'.EstimateLine::gen_number($line['id'], $lines).'</td>
								<td rowspan="'.$count.'" class = "tabletd">' . htmlspecialchars($line['ficha_description']) . '</td>
								<td rowspan="'.$count.'" class = "align-mid tabletd">' . $un . '</td>
								<td rowspan="'.$count.'" class = "align-mid tabletd">' . ($un=="" ? "" :    number_format($line['ficha_quantity'], 2, $dec_point = ",", $thousands_sep = ".") ) . '</td>
							    <td rowspan="'.$count.'" class="tabletd">' . htmlspecialchars($line['ficha_note']) . '</td>
							    <td class="tabletd">'.($count>0 ? $maodeobra->first()->user->name : '').'</td>
                                <td class="tabletd">'.($count>0 && $maodeobra->first()->hours != 0 ? $maodeobra->first()->hours : '').'</td>
                                <td class="tabletd"></td>
                                <td class="tabletd"></td>
                                <td class="tabletd"></td>
							  </tr>';
                    foreach($maodeobra->limit(10000)->offset(1)->get() as $mao){
                        $table_main .= '<tr nobr="true">
                                <td class="tabletd">'.$mao->user->name.'</td>
                                <td class="tabletd">'.($mao->hours != 0 ? $mao->hours : '').'</td>
                                <td class="tabletd"></td>
                                <td class="tabletd"></td>
                                <td class="tabletd"></td>
                            </tr>';
                    }
                    break;
            }
        }


        $html='<!DOCTYPE html>
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
                            #additional-page *{
                                font-family: Arial;
                                font-size: 16px;
                            }
                            #additional-page hr{
                                border-color: #000;
                            }
                            html, body{
                                margin:0;
                                padding:0;
                            }
                            table { page-break-inside:auto; margin-top: 5px; }
                            tr { page-break-inside:avoid; page-break-after:auto }
                            thead { display:table-header-group; }
                            tfoot { display:table-footer-group }
							.page-wrapper{
								padding: 50px;
								padding-top: 20px;
								padding-right: 35px;
							}
							.table-main {
							    border-collapse: collapse;
							    padding: 5px;
							}
							.table-main td {
							    padding: 5px;
							}
							.align-mid{
								text-align: center;
							    vertical-align: middle;
							}
							.full-width{
								width: 100%;
							}
							.left {
							    text-align: left;
							}
							.right{
								text-align: right;
							}
							.bold{
								font-weight: bold;
							}
							.red{
								color: red;
							}
							.background-gray-light{
								background-color: #EFEFEF;
							}
							.background-blue-light{
								background-color: #EFF3FF;
							}
							.background-gray-dark{
								background-color: #AAAAAA;
							}
							
							.bottom-general-info{
								margin-top: 50px;
							}
							.bottom{
								padding: 2px;
								padding-left: 50px;
							}
							.top td{
							    padding-top: 5px;
							}
							.tabletd{
							    border: 0.1px solid black;
							    vertical-align: top;
							}
						</style>
					</head>
					<body>
						<div class = "page-wrapper">
							<div class = "top-general-info">
								<table class = "top">
								    <thead></thead>
									<tbody>
										' . $table_top . '
									</tbody>
								</table>
							</div>
							<div class = "main-table">
								<table style="page-break-after: always;" class = "table-main full-width">
									<thead>
										<tr>
											<td class = "align-mid" style = "width: 3%; border: 0.1px solid black">
												Nº
											</td>
											<td class = "align-mid" style = "width: 20%; border: 0.1px solid black">
												'.trans('estimate::estimate.Description').'
											</td>
											<td class = "align-mid" style = "width: 3%; border: 0.1px solid black">
												'.trans('estimate::estimate.Un').'
											</td>
											<td class = "align-mid" style = "width: 3%; border: 0.1px solid black">
												'.trans('estimate::estimate.Quant').'
											</td>
											<td class = "align-mid" style = "width: 8%; border: 0.1px solid black">
												'.trans('estimate::estimate.Notes').'
											</td>
											<td class = "align-mid" style = "width: 10%; border: 0.1px solid black">
												'.trans('estimate::estimate.Person').'
											</td>
											<td class = "align-mid" style = "width: 5%; border: 0.1px solid black">
												'.trans('estimate::estimate.Hours').'
											</td>
											<td class = "align-mid" style = "width: 7%; border: 0.1px solid black">
												'.trans('estimate::estimate.Start_time').'
											</td>
											<td class = "align-mid" style = "width: 7%; border: 0.1px solid black">
												'.trans('estimate::estimate.Finish_time').'
											</td>
											<td class = "align-mid" style = "width: 15%; border: 0.1px solid black">
												'.trans('estimate::estimate.Delay_because').'
											</td>
										</tr>
									</thead>
									<tbody>
										' . $table_main . '
									</tbody>
								</table>
								<table style="width: 100%;border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td colspan="2" style="font-size: 18px;" class="tabletd align-mid bold background-gray-dark bold">'.trans('estimate::estimate.Delays_notes').'</td>
                                        </tr>
                                        <tr>
                                            <td class="tabletd" style="width:3%"></td>
                                            <td class="tabletd" style="height: 180px;text-align: center;"></td>
                                        </tr>
                                        <tr>
                                            <td class="tabletd" style="width:3%"></td>
                                            <td class="tabletd" style="height: 180px;text-align: center;"></td>
                                        </tr>
                                        <tr>
                                            <td class="tabletd" style="width:3%"></td>
                                            <td class="tabletd" style="height: 180px;text-align: center;"></td>
                                        </tr>
                                        <tr>
                                            <td class="tabletd" style="width:3%"></td>
                                            <td class="tabletd" style="height: 180px;text-align: center;"></td>
                                        </tr>
                                    </tbody>
                                </table>
							</div>
						</div>
					</body>
				</html>';

        return $html;
    }

}
