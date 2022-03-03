<?php

namespace Rkesa\Estimate\Http\Helpers;

use App\GlobalSettings;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Response;
use Exception;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Illuminate\Support\Facades\Log;

class EstimatePDFCreator {

    public function render_pdf($data, $system = false, $type = 'normal'){
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

        $header = '<!DOCTYPE html><head><meta charset="utf-8"></head> <body style="height:73px;overflow:hidden;margin:0;padding:0;"><span style="font-size: 12px; margin-left: 50px;">'.trans('estimate::estimate.estimate_number').': '.$data->get_estimate_number().'</span>; <span style="font-size: 12px;">'.trans('estimate::estimate.Created_by').': '.($data->user_id ? $data->user->name : '').'; </span><span style="font-size: 12px;">'.trans('estimate::estimate.Data').': '.$data->updated_at->format('Y-m-d').'; </span><img style="margin-top:15px;display:block;float:right;margin-right:35px;max-width:260px;max-height:60px;" src="'.public_path().GlobalSettings::first()->site_logo.'"/></body></html>';

        $gs = GlobalSettings::first();

        $pdf = new Pdf(array(
            'no-outline',         // Make Chrome not complain
            'margin-right'  => 0,
            'margin-left'   => 0,
            'header-html' => $header,
            'header-spacing' => 2,
            'footer-html' => $footer,
            'orientation' => $gs->estimate_orientation == 1 ? 'landscape' : 'portrait'
        ));

        $pdf->addPage($this->render($data, $system, $type));

        $result = $pdf->toString();
        if ($result === false) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        return $result;
    }

    public function render_html($data){
        return Response($this->render($data));
    }

    public function render($data, $system = false, $type = 'normal'){
        $pay_stages = '';
        foreach ($data->estimate_pay_stages as $pay_stage){
            $pay_stages .= $pay_stage->percent.'% '.$pay_stage->text.' ';
        }

        $gs = GlobalSettings::first();
        $symbol = currency()->getCurrency($gs->currency)['symbol'];

        $table_top = '<tr>
					<td class = "padding-top" style = "width: 15%">'.trans('estimate::estimate.estimate_number').':</td>
					<td class = "padding-top">' . $data->get_estimate_number() .'</td>
				  </tr>
				  <tr>
					<td class = "padding-top" style = "width: 15%">'.trans('estimate::estimate.Assunto').':</td>
					<td class = "padding-top">' . htmlspecialchars($data->subject) . '</td>
				  </tr>
				  <tr>
					<td class = "padding-top" style = "width: 15%">'.trans('estimate::estimate.Obra_situada_em').':</td>
					<td class = "padding-top">' . htmlspecialchars($data->service->address) . '</td>
				  </tr>
				  <tr>
					<td class = "padding-top" style = "width: 15%">'.trans('estimate::estimate.Created_by').':</td>
					<td class = "padding-top">' . ($data->user_id ? $data->user->name : '') . '</td>
				  </tr>
				  <tr>
					<td class = "padding-top" style = "width: 15%">'.trans('estimate::estimate.Data').':</td>
					<td class = "padding-top">' . $data->updated_at->format('Y-m-d') . '</td>
				  </tr>
				  <tr>
					<td colspan="2"> </td>
				  </tr>
				  <tr>
					<td colspan="2" class = "padding-top">'.trans('estimate::estimate.Estimate_intro_text').'</td>
				  </tr>
				  <tr>
					<td colspan="2" style="height:15px;"> </td>
				  </tr>';

        $table_main = "";
        $lines = $data->lines_with_join();
        $lines = EstimateLine::array_to_tree_and_to_array($lines);
        $begin_of_estimate = true;
        $current_total_price = 0.0;
        $full_price = 0.0;
        foreach ($lines as $line) {
            switch ($line['lineable_type']) {
                case '\App\EstimateLineSeparator':
                    $table_main .= '<tr nobr="true"><td colspan="7" class = "align-mid bold background-gray-dark" style = "border: 0.1px solid black">' . htmlspecialchars($line['separator_name']) . '</td></tr>';
                    break;
                case '\App\EstimateLineCategory':
                    if (!$begin_of_estimate && $line['parent_id']==null){
                        $table_main .= '<tr nobr="true">
								<td colspan="5" class="align-mid">'.trans('estimate::estimate.TOTAL').'</td>
								<td colspan="2" class="align-mid">'.number_format($current_total_price, 2, $dec_point = ",", $thousands_sep = ".").' '.$symbol.'</td>
							 </tr>';
                        $full_price += $current_total_price;
                        $current_total_price = 0.0;
                    } else {
                        $begin_of_estimate = false;
                    }

                    $table_main .= '<tr nobr="true">
								<td class = "align-mid bold background-gray-light" style = "width: 7%; border: 0.1px solid black">'.EstimateLine::gen_number($line['id'], $lines). ($system ? '(lineable_id: '.$line['id'].')' : '').'</td>
								<td colspan="6" class = "left bold background-gray-light" style = "width: 93%; border: 0.1px solid black">' . htmlspecialchars($line['category_name']) . '</td>
							 </tr>';
                    break;
                case '\App\EstimateLineData':
                    $un = EstimateUnit::convertUnit($line['data_measure']);
                    $data_line_price = self::data_with_additional($line['data_ppu'], $data->additional_price);
                    $data_total_price = round($data_line_price*$line['data_quantity'], 2);
                    $table_main .= '<tr nobr="true">
								<td class = "align-mid" style = "width: 7%; border: 0.1px solid black">'.EstimateLine::gen_number($line['id'], $lines). ($system ? '(lineable_id: '.$line['id'].')' : '') . '</td>
								<td class = "" style = "width: 37%; border: 0.1px solid black">' . htmlspecialchars($line['data_description']) . '</td>
								<td class = "align-mid" style = "width: 6%; border: 0.1px solid black">' . $un . '</td>
								<td class = "align-mid" style = "width: 12%; border: 0.1px solid black">' . ($un=="" ? "" :  ((number_format($data_line_price, 2, $dec_point = ",", $thousands_sep = ".") != "") ?  number_format($data_line_price, 2, $dec_point = ",", $thousands_sep = ".") . ' '.$symbol : "") ) .'</td>
								<td class = "align-mid" style = "width: 6%; border: 0.1px solid black">' . ($un=="" ? "" :  number_format($line['data_quantity'], 2, $dec_point = ",", $thousands_sep = ".") ) . '</td>
								<td class = "align-mid" style = "width: 12%; border: 0.1px solid black">' . ($un=="" ? "" :  ((number_format($data_total_price, 2, $dec_point = ",", $thousands_sep = ".") != "") ?  number_format($data_total_price, 2, $dec_point = ",", $thousands_sep = ".") . ' '.$symbol : "") ) .'</td>
								<td class = "align-mid" style = "width: 20%; border: 0.1px solid black">' . htmlspecialchars($line['data_note']) . '</td>
							  </tr>';
                    $current_total_price += $data_total_price;
                    break;
                case '\App\EstimateLineFicha':
                    $un = EstimateUnit::convertUnit($line['ficha_measure']);
                    $ficha_line_price =  self::data_with_additional($line['ficha_ppu'], $data->additional_price);
                    $ficha_total_price = round($ficha_line_price*$line['ficha_quantity'], 2);
                    $table_main .= '<tr nobr="true">
								<td class = "align-mid" style = "width: 7%; border: 0.1px solid black">'.EstimateLine::gen_number($line['id'], $lines). ($system ? '(lineable_id: '.$line['id'].')' : '').'</td>
								<td class = "" style = "width: 37%; border: 0.1px solid black">' . htmlspecialchars($line['ficha_description']) . '</td>
								<td class = "align-mid" style = "width: 6%; border: 0.1px solid black">' . $un . '</td>
								<td class = "align-mid" style = "width: 12%; border: 0.1px solid black">' . ($un=="" ? "" :   ((number_format($ficha_line_price, 2, $dec_point = ",", $thousands_sep = ".") != "") ?  number_format($ficha_line_price, 2, $dec_point = ",", $thousands_sep = ".") . ' '.$symbol : "") ).'</td>
								<td class = "align-mid" style = "width: 6%; border: 0.1px solid black">' . ($un=="" ? "" :    number_format($line['ficha_quantity'], 2, $dec_point = ",", $thousands_sep = ".") ) . '</td>
								<td class = "align-mid" style = "width: 12%; border: 0.1px solid black">' . ($un=="" ? "" :    ((number_format($ficha_total_price, 2, $dec_point = ",", $thousands_sep = ".") != "") ?  number_format($ficha_total_price, 2, $dec_point = ",", $thousands_sep = ".") . ' '.$symbol : "") ) .'</td>
								<td class = "align-mid" style = "width: 20%; border: 0.1px solid black">' . htmlspecialchars($line['ficha_note']) . '</td>
							  </tr>';
                    $current_total_price += $ficha_total_price;
                    break;
            }
        }
        $table_main .= '<tr nobr="true">
								<td colspan="5" class="align-mid">'.trans('estimate::estimate.TOTAL').'</td>
								<td colspan="2" class="align-mid">'.number_format($current_total_price, 2, $dec_point = ",", $thousands_sep = ".").' '.$symbol.'</td>
							 </tr>';

        $full_price += $current_total_price;

        $vatHtml = ($data->vat == "" || $data->vat_options) ? "" : '<tr>
					<td class = "background-gray-light bold bottom">'.trans('estimate::estimate.IVA').'</td>
				</tr>
				<tr>
					<td class = "bottom">' . $data->vat . '</td>
				</tr>';

        $conditions = GlobalSettings::first()->estimate_conditions_text;

        $additional_lines = '<p style="page-break-after: always;">&nbsp;</p>
                             <p style="page-break-before: always;">&nbsp;</p>
							<table class="bottom mybottom" style="width:100%;">
							    <thead></thead>
								<tbody>
									' /*. $empty_lines*/ . '
									<tr><td colspan = "2"><h2 style="font-weight: bold">'.__('estimate::estimate.Acceptance_and_confirmation_of_budget_No_and_of_the_above_mentioned_conditions', ['estimate_number' => $data->get_estimate_number()]).'</h2></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td colspan = "2">'.trans('estimate::estimate.Customer_billing').':<hr></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td colspan = "2">'.trans('estimate::estimate.Morada').':<hr></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td colspan = "1" style="width: 45%;">'.trans('estimate::estimate.NIF').':<hr></td><td>'.trans('estimate::estimate.Postal_code').':<hr></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td>'.trans('estimate::estimate.Contact').':<hr></td><td colspan = "1">Email:<hr></td></tr>


									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td colspan = "2">'.trans('estimate::estimate.Represented_by').':<hr></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td colspan = "1">'.trans('estimate::estimate.NIF').':<hr></td><td colspan = "1">'.trans('estimate::estimate.Contact').':<hr></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td>Email:<hr></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td colspan = "1">'.trans('estimate::estimate.Signature').':<hr></td></tr>
									<tr><td colspan = "2"><!-- blank line --></td></tr>
									<tr><td>'.trans('estimate::estimate.Data').':&nbsp;&nbsp;&nbsp;&nbsp; ______________/______________/________________</td></tr>
								</tbody>
							</table>';

        $table_bottom = ($pay_stages == '' ? '' : '<tr>
						<td class = "background-gray-light bold bottom">'.trans('estimate::estimate.Pagamento_de_prestacoes').'</td>
					</tr>
					<tr>
						<td class = "bottom table_main">' . $pay_stages . '</td>
					</tr>
					<tr>
						<td>
							<!-- blank line -->
						</td>
					</tr>
					')
                    .($data->deadline && $data->deadline != 0 ?
                        '<tr>
                            <td class = "background-gray-light bold bottom">'.trans('estimate::estimate.Prazo').'</td>
                        </tr>
                        <tr>
                            <td class = "bottom">'.trans('estimate::estimate.The_estimated_period_for_the_execution_of_the_total_work_is').': ' . $data->deadline . ' '.trans_choice('template.day', $data->deadline).'</td>
                        </tr>' : ''
                    ).
                    '<tr>
						<td>
							<!-- blank line -->
						</td>
					</tr>
					'. $vatHtml .'
					<tr>
						<td>
							<!-- blank line -->
						</td>
					</tr>
					'.(self::IsNullOrEmptyString($conditions) ? '' :
                        '<tr >
                            <td class = "background-gray-light bold bottom">'.trans('estimate::estimate.Conditions').'</td>
                        </tr>
                        <tr>
                            <td class = "bottom">' . $conditions . '</td>
                        </tr>'
                    ).'
					<tr>
						<td>
							<!-- blank line -->
						</td>
					</tr>';

        $new_iva = '';

        $base_price = $full_price;

        if($data->discount){
            $price = $full_price - $full_price * ($data->discount / 100);
        }else{
            $price = $full_price;
        }

        if($data->discount){
            $discount = '<tr nobr="true">
					<td colspan="1" style = "width: 7%;"></td>
					<td colspan="1" style = "width: 37%; border: 0.1px solid red">'.__('estimate::estimate.The_total_value_of_the_above_described_commercially_discounted_jobs_is', ['percent' => number_format($data->discount, 2, $dec_point = ",", $thousands_sep = ".")]).'</td>
					<td colspan="2" style = "width: 18%; border: 0.1px solid red"><span style="color:red">' . number_format($price, 2, $dec_point = ",", $thousands_sep = ".") . ' '.$symbol.'</span></td>
					</tr>';
        }else{
            $discount = '';
        }

        switch($data->vat_type){
            case 1:
                $iva_6 = ($price * ($data->vat_maodeobra / 100)) * 0.06;
                $iva_23 = ($price * ($data->vat_material / 100)) * 0.23;

                $global = $price + $iva_6 + $iva_23;
                $new_iva = '<tr nobr="true">
                            <td style = "width: 7%"></td>
                            <td colspan="1" style = "width: 37%; border: 0.1px solid red">'.__('estimate::estimate.VAT_percent_for_labor', ['percent' => '6']).': </td>
                            <td colspan="2" style = "width: 18%; border: 0.1px solid red">' . number_format($iva_6, 2, ",", ".") . ' '.$symbol.'</td>
                        </tr>';
                $new_iva .= '<tr nobr="true">
                            <td style = "width: 7%"></td>
                            <td colspan="1" style = "width: 37%; border: 0.1px solid red">'.__('estimate::estimate.VAT_percent_referring_to_material', ['percent' => '23']).': </td>
                            <td colspan="2" style = "width: 18%; border: 0.1px solid red">' . number_format($iva_23, 2, ",", ".") . ' '.$symbol.'</td>
                        </tr>';
                $new_iva .= '<tr nobr="true">
                            <td style = "width: 7%;"></td>
                            <td colspan="1" style = "width: 37%; border: 0.1px solid red; background-color: #DEDEDE;">'.trans('estimate::estimate.TOTAL_VALUE_WITH_VAT').': </td>
                            <td colspan="2" style = "width: 18%; border: 0.1px solid red; background-color: #DEDEDE;"><span style = "color:red">' . number_format($global, 2, ",", ".") . ' '.$symbol.'</span></td>
                        </tr>';
                break;
            case 2:
                $new_iva = '<tr nobr="true">
                            <td style = "width: 7%"></td>
                            <td colspan="1" style = "width: 37%; border: 0.1px solid red">'.trans('estimate::estimate.IVA').': </td>
                            <td colspan="2" style = "width: 18%; border: 0.1px solid red">'.trans('estimate::estimate.Auto_liquidacao').'</td>
                        </tr>';
                $new_iva .= '<tr nobr="true">
                            <td style = "width: 7%"></td>
                            <td colspan="1" style = "width: 37%; border: 0.1px solid red; background-color: #DEDEDE;">'.trans('estimate::estimate.TOTAL_VALUE_WITH_VAT').': </td>
                            <td colspan="2" style = "width: 18%; border: 0.1px solid red; background-color: #DEDEDE;"><span style = "color:red">' . number_format($price, 2, ",", ".") . ' '.$symbol.'</span></td>
                        </tr>';
                break;
            case 3:
                $iva_23 = $price * 0.23;
                $global = $price + $iva_23;
                $new_iva .= '<tr nobr="true">
                            <td style = "width: 7%"></td>
                            <td colspan="1" style = "width: 37%; border: 0.1px solid red">'.trans('estimate::estimate.IVA').' 23%: </td>
                            <td colspan="2" style = "width: 18%; border: 0.1px solid red">' . number_format($iva_23, 2, ",", ".") . ' '.$symbol.'</td>
                        </tr>';
                $new_iva .= '<tr nobr="true">
                            <td style = "width: 7%"></td>
                            <td colspan="1" style = "width: 37%; border: 0.1px solid red; background-color: #DEDEDE;">'.trans('estimate::estimate.TOTAL_VALUE_WITH_VAT').': </td>
                            <td colspan="2" style = "width: 18%; border: 0.1px solid red; background-color: #DEDEDE;"><span style = "color:red">' . number_format($global, 2, ",", ".") . ' '.$symbol.'</span></td>
                        </tr>';
                break;
            case 4:
                break;
            case 5:
                $new_iva = '<tr nobr="true">
                            <td style = "width: 7%"></td>
                            <td colspan="1" style = "width: 37%; border: 0.1px solid red">'.trans('estimate::estimate.IVA').': </td>
                            <td colspan="2" style = "width: 18%; border: 0.1px solid red">'.trans('estimate::estimate.Intra_community').'</td>
                        </tr>';
                break;
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
                                font-size: 15px;
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
							.mybottom td{
							    padding-top: 15px;
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
								<table class = "table-main full-width">
									<thead>
										<tr>
											<td class = "align-mid" style = "width: 7%; border: 0.1px solid black">
												Nº
											</td>
											<td class = "align-mid" style = "width: 37%; border: 0.1px solid black">
												'.trans('estimate::estimate.Description').'
											</td>
											<td class = "align-mid" style = "width: 6%; border: 0.1px solid black">
												'.trans('estimate::estimate.Un').'
											</td>
											<td class = "align-mid" style = "width: 12%; border: 0.1px solid black">
												'.trans('estimate::estimate.PU').'
											</td>
											<td class = "align-mid" style = "width: 6%; border: 0.1px solid black">
												'.trans('estimate::estimate.Quant').'
											</td>
											<td class = "align-mid" style = "width: 12%; border: 0.1px solid black">
												'.trans('estimate::estimate.Valor').'
											</td>
											<td class = "align-mid" style = "width: 20%; border: 0.1px solid black">
												'.trans('estimate::estimate.Notes').'
											</td>
										</tr>
									</thead>
									<tbody>
										' . $table_main . '
						  			  <tr>
										<td class = "align-mid bold right red background-blue-light" colspan="5" style = "width: 68%; background-color: #EFF3FF; border: 0.1px solid black">'.trans('estimate::estimate.TOTAL_DO_ORCAMENTO').': </td>
										<td class = "align-mid bold left red background-blue-light" colspan="2" style = "width: 32%; background-color: #EFF3FF; border: 0.1px solid black">' . number_format($base_price, 2, $dec_point = ",", $thousands_sep = ".") . ' '.$symbol.'</td>
									  </tr>
										<tr nobr="true">
											<td style = "width: 7%"></td>
											<td colspan="1" style = "width: 37%; border: 0.1px solid red">'.trans('estimate::estimate.The_total_value_of_the_work_described_above_is').'</td>
											<td colspan="2" style = "width: 18%; border: 0.1px solid red"><span style = "color:red">' . number_format($base_price, 2, $dec_point = ",", $thousands_sep = ".") . ' '.$symbol.'</span></td>
										</tr>
										' . $discount . '
										' . $new_iva . '
									</tbody>
								</table>
							</div>
							<div class = "bottom-general-info">
								<table class = "full-width">
								    <thead></thead>
									<tbody>
										' . $table_bottom . '
									</tbody>
								</table>
							</div>'.
                            (GlobalSettings::first()->estimate_show_contract ?
                                '<div id="additional-page" class = "bottom-general-info">
                                    ' . $additional_lines . '
                                </div>' : ''
                            ).
                        '</div>
					</body>
				</html>';

        return $html;
    }

    private function IsNullOrEmptyString($question){
        return (!isset($question) || trim($question)==='');
    }

    private function data_with_additional($data, $ad){
        return round($data*(1+$ad/100.0), 2);
    }
}
