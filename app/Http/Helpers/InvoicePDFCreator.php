<?php

namespace App\Http\Helpers;

use Exception;
use Carbon\Carbon;
use App\GlobalSettings;
use Illuminate\Support\Str;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Log;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Illuminate\Support\Facades\Response;

class InvoicePDFCreator
{

    public function render_pdf($data, $system = false, $type = 'normal', $company_name, $country, $city, $address, $postcode, $sum, $modules, $number_of_workers, $payment_id){
        $gs = GlobalSettings::first();

        $pdf = new Pdf(array(
            'no-outline',         // Make Chrome not complain
            'margin-right'  => 0,
            'margin-left'   => 0,
            'header-spacing' => 2,
            'orientation' => 'portrait'
        ));

        $pdf->addPage($this->render($data, $system, $type, $company_name, $country, $city, $address, $postcode, $sum, $modules, $number_of_workers, $payment_id));

        $file_name = Str::uuid()->toString().".pdf";
        $pdf->saveAs(public_path("invoices/".$file_name));
        $result = $pdf->toString();
        if ($result === false) {
            throw new Exception('Could not create PDF: '.$pdf->getError());
        }

        return ["result" => $result, "name" => $file_name];
    }

    public function render_html($data, $company_name, $country, $city, $address, $postcode, $sum, $modules, $number_of_workers, $payment_id){
        return Response($this->render($data,$company_name, $country, $city, $address, $postcode, $sum, $modules, $number_of_workers, $payment_id));
    }

    public function render(
        $data, 
        $system = false, 
        $type = 'normal', 
        $company_name, 
        $country, 
        $city, 
        $address, 
        $postcode, 
        $sum,
        $modules,
        $number_of_workers, 
        $payment_id){
        $gs = GlobalSettings::first();
        $symbol = currency()->getCurrency($gs->currency)['symbol'];

        $modules_text = "";
        foreach($modules as $mod)
        {
            $modules_text = $modules_text.trans('template.module-'.$mod->name, [], 'en').', ';
        }

        $modules_text = $modules_text.Carbon::parse($modules[0]->current_subscription_date_start)->toFormattedDateString()." - ".Carbon::parse($modules[0]->current_subscription_date_end)->toFormattedDateString();
  
        $html='
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
            </style>
        </head>
        <body>
            <div class="container">
                <svg width="200" height="200"
                    xmlns:dc="http://purl.org/dc/elements/1.1/"
                    xmlns:cc="http://creativecommons.org/ns#"
                    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                    xmlns:svg="http://www.w3.org/2000/svg"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 991.94666 608.34668"
                    height="608.34668"
                    width="991.94666"
                    xml:space="preserve"
                    id="svg2"
                    version="1.1"><metadata
                      id="metadata8"><rdf:RDF><cc:Work
                          rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type
                            rdf:resource="http://purl.org/dc/dcmitype/StillImage" /></cc:Work></rdf:RDF></metadata><defs
                      id="defs6" /><g
                      transform="matrix(1.3333333,0,0,-1.3333333,0,608.34667)"
                      id="g10"><g
                        transform="scale(0.1)"
                        id="g12"><path
                          id="path14"
                          style="fill:#488e90;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 1863.65,388 -171.58,89.379 v 0 l -514.14,267.82 -507.621,264.421 v 672.92 0.23 l 1015.241,576.56 v -1.71 l 6.52,3.69 v 787.51 L 0,2089.41 V 672.34 L 1181.19,0 1686.8,287.461 v 0 L 1863.65,388" /><path
                          id="path16"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="M 2362.32,4562.55 1692.07,4182.89 V 2261.31 l -6.52,-3.69 V 1011.35 L 1177.93,745.199 1692.07,477.379 1863.65,388 2362.38,671.551 V 2640.99 4562.52 l -0.06,0.03" /><path
                          id="path18"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 2609,666.57 v 2168.78 h 517.5 V 666.57 H 2609" /><path
                          id="path20"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="M 5200.58,1832.03 V 809.691 C 5059.42,739.09 4921.91,685.93 4788,650.18 4654.11,614.41 4524.3,596.5 4398.65,596.5 c -320.01,0 -585.64,105.621 -796.86,316.871 -211.26,211.229 -316.87,477.829 -316.87,799.769 0,324.84 107.07,593.58 321.22,806.29 214.14,212.68 485.08,319.03 812.82,319.03 123.73,0 241.67,-16.93 353.84,-50.76 112.13,-33.86 219.91,-85.09 323.39,-153.7 l -53.68,-526.43 c -97.66,98.61 -194.82,170.87 -291.48,216.8 -96.68,45.92 -198.2,68.9 -304.53,68.9 -188.51,0 -344.67,-63.09 -468.4,-189.26 -123.76,-126.15 -185.6,-284 -185.6,-473.46 0,-199.19 59.67,-361.82 179.08,-487.98 119.39,-126.18 273.34,-189.25 461.88,-189.25 43.5,0 87.46,3.85 131.96,11.59 44.45,7.74 90.85,18.86 139.21,33.36 v 305.98 h -398.79 v 427.78 h 894.74" /><path
                          id="path22"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 6073.58,1333.18 h 552.49 l -272.63,658.4 z M 5287.57,664.672 6359.25,2832.65 h 31.91 L 7439.62,664.672 h -524.97 l -133.4,305.976 H 5928.57 L 5792.24,664.672 h -504.67" /><path
                          id="path24"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="M 2614.02,57.7695 V 371.512 h 95.46 l 70.25,-219.582 h 1.29 l 70.89,219.582 h 96.32 V 57.7695 H 2875.4 V 259.891 l -1.29,0.218 -69.17,-202.3395 h -48.91 l -67.88,199.3205 -1.29,-0.211 V 57.7695 h -72.84" /><path
                          id="path26"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 3065.23,186.422 c 0,-22.711 5.28,-41.231 15.81,-55.602 10.54,-14.359 25.63,-21.539 45.28,-21.539 19.08,0 33.85,7.18 44.31,21.539 10.47,14.371 15.71,32.891 15.71,55.602 v 56.879 c 0,22.41 -5.28,40.789 -15.82,55.168 -10.53,14.351 -25.41,21.543 -44.63,21.543 -19.64,0 -34.66,-7.153 -45.06,-21.442 -10.4,-14.289 -15.6,-32.718 -15.6,-55.269 z m -72.82,0 v 56.449 c 0,38.5 12.32,70.309 36.98,95.461 24.65,25.137 56.82,37.707 96.5,37.707 39.4,0 71.45,-12.57 96.19,-37.707 24.72,-25.152 37.09,-56.961 37.09,-95.461 v -56.449 c 0,-38.652 -12.26,-70.5 -36.77,-95.5704 -24.51,-25.0703 -56.54,-37.6016 -96.08,-37.6016 -39.82,0 -72.1,12.5313 -96.82,37.6016 -24.73,25.0704 -37.09,56.9184 -37.09,95.5704" /><path
                          id="path28"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 3376.17,113.801 h 42.02 c 16.52,0 30.13,6.75 40.83,20.25 10.7,13.508 16.06,30.961 16.06,52.371 v 56.879 c 0,21.109 -5.36,38.429 -16.06,51.929 -10.7,13.5 -24.31,20.258 -40.83,20.258 h -42.02 z M 3303.34,57.7695 V 371.512 h 114.75 c 36.88,0 67.74,-12.043 92.57,-36.09 24.83,-24.063 37.25,-54.91 37.25,-92.551 v -56.449 c 0,-37.793 -12.42,-68.684 -37.25,-92.6603 -24.83,-23.9922 -55.69,-35.9922 -92.57,-35.9922 h -114.75" /><path
                          id="path30"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 3589.71,168.102 v 203.41 h 72.83 v -203.41 c 0,-19.403 5.04,-34.051 15.11,-43.961 10.08,-9.911 24.04,-14.86 41.89,-14.86 17.7,0 31.48,4.91 41.33,14.75 9.87,9.84 14.8,24.528 14.8,44.071 v 203.41 h 72.83 v -203.41 c 0,-35.911 -11.91,-64.043 -35.74,-84.3637 -23.83,-20.3281 -54.9,-30.4883 -93.22,-30.4883 -38.61,0 -69.91,10.1602 -93.88,30.4883 -23.97,20.3207 -35.95,48.4527 -35.95,84.3637" /><path
                          id="path32"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="M 3896.99,57.7695 V 371.512 h 72.83 V 113.801 h 134.67 V 57.7695 h -207.5" /><path
                          id="path34"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 4235.28,174.781 h 59.69 l -29.31,97.399 h -1.29 z M 4123.66,57.7695 4226.01,371.512 h 39 v -0.211 l 0.22,0.211 h 39 L 4406.8,57.7695 h -76.71 l -18.32,60.9805 h -93.3 l -18.1,-60.9805 h -76.71" /><path
                          id="path36"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 4502.05,234.031 h 36.84 c 14.65,0 25.85,3.34 33.61,10.02 7.76,6.679 11.64,16.269 11.64,28.769 0,12.789 -3.99,23.09 -11.96,30.918 -7.98,7.832 -19.22,11.75 -33.73,11.75 h -36.4 z M 4429.21,57.7695 V 371.512 h 109.35 c 36.38,0 65.2,-8.403 86.49,-25.211 21.27,-16.813 31.92,-39.801 31.92,-68.961 0,-16.231 -4.29,-30.16 -12.86,-41.789 -8.58,-11.641 -21.22,-21.129 -37.93,-28.449 19.3,-5.461 33.16,-14.543 41.6,-27.262 8.43,-12.711 12.64,-28.551 12.64,-47.52 v -19.808 c 0,-8.192 1.22,-17.5315 3.66,-28.0237 2.44,-10.4883 6.68,-17.957 12.71,-22.4063 v -4.3125 h -75.37 c -5.76,4.461 -9.51,12.2891 -11.24,23.4922 -1.73,11.1992 -2.6,21.7693 -2.6,31.6793 v 18.95 c 0,14.949 -3.94,26.371 -11.84,34.269 -7.9,7.899 -19.48,11.852 -34.7,11.852 h -38.99 V 57.7695 h -72.84" /><path
                          id="path38"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="M 4845.1,57.7695 V 371.512 h 205.57 v -56.024 h -132.74 v -67.449 h 110.54 v -56.027 h -110.54 v -78.211 h 132.51 V 57.7695 H 4845.1" /><path
                          id="path40"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 5164.22,234.031 h 36.85 c 14.65,0 25.85,3.34 33.61,10.02 7.76,6.679 11.63,16.269 11.63,28.769 0,12.789 -3.98,23.09 -11.95,30.918 -7.98,7.832 -19.22,11.75 -33.73,11.75 h -36.41 z M 5091.39,57.7695 V 371.512 h 109.35 c 36.37,0 65.2,-8.403 86.49,-25.211 21.27,-16.813 31.92,-39.801 31.92,-68.961 0,-16.231 -4.29,-30.16 -12.87,-41.789 -8.57,-11.641 -21.22,-21.129 -37.93,-28.449 19.31,-5.461 33.17,-14.543 41.61,-27.262 8.42,-12.711 12.63,-28.551 12.63,-47.52 v -19.808 c 0,-8.192 1.22,-17.5315 3.66,-28.0237 2.45,-10.4883 6.68,-17.957 12.72,-22.4063 v -4.3125 h -75.37 c -5.76,4.461 -9.51,12.2891 -11.24,23.4922 -1.73,11.1992 -2.6,21.7693 -2.6,31.6793 v 18.95 c 0,14.949 -3.94,26.371 -11.84,34.269 -7.91,7.899 -19.48,11.852 -34.7,11.852 h -39 V 57.7695 h -72.83" /><path
                          id="path42"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 5445.64,221.32 h 46.97 c 15.8,0 27.86,4.34 36.2,13.039 8.33,8.692 12.49,19.86 12.49,33.5 0,13.942 -4.13,25.352 -12.38,34.27 -8.26,8.91 -20.37,13.359 -36.31,13.359 h -46.97 z M 5372.81,57.7695 V 371.512 h 119.91 c 37.24,0 66.78,-9.481 88.63,-28.442 21.86,-18.968 32.79,-43.89 32.79,-74.781 0,-30.879 -10.93,-55.769 -32.8,-74.66 -21.86,-18.891 -51.42,-28.34 -88.67,-28.34 h -47.03 V 57.7695 h -72.83" /><path
                          id="path44"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 5766.96,157.32 0.43,1.289 h 70.89 c 0,-18.238 4.48,-31.211 13.44,-38.879 8.97,-7.691 22.7,-11.539 41.2,-11.539 15.19,0 26.51,3.02 33.96,9.059 7.46,6.031 11.18,13.93 11.18,23.699 0,11.199 -3.65,19.781 -10.97,25.75 -7.3,5.961 -20.55,12.313 -39.76,19.071 -38.9,12.64 -67.82,26.109 -86.77,40.402 -18.94,14.289 -28.42,34.879 -28.42,61.726 0,26.293 11,47.551 32.97,63.793 21.98,16.231 49.92,24.348 83.83,24.348 35.76,-0.148 64.56,-8.551 86.41,-25.219 21.11,-16.23 31.67,-38.859 31.67,-67.871 0,-1.008 0,-1.937 0,-2.801 l -0.43,-1.289 h -70.68 c 0,14.789 -4.01,25.532 -12.04,32.2 -8.03,6.679 -20.13,10.019 -36.33,10.019 -13.18,0 -23.57,-3.226 -31.17,-9.687 -7.6,-6.461 -11.39,-14.442 -11.39,-23.922 0,-9.188 3.9,-16.547 11.71,-22.078 7.82,-5.539 22.4,-12.469 43.75,-20.801 36.28,-10.77 63.76,-23.918 82.45,-39.43 18.67,-15.512 28,-36.769 28,-63.781 0,-27.578 -10.76,-49.1602 -32.28,-64.7501 -21.53,-15.5898 -50.08,-23.3789 -85.67,-23.3789 -35.44,0.1406 -65.51,8.5508 -90.22,25.2109 -23.98,16.2305 -35.91,41.5081 -35.76,75.8481 0,1 0,2.011 0,3.011" /><path
                          id="path46"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 6024.48,371.512 h 78.85 l 62.39,-137.051 h 1.3 l 62.59,137.051 h 78.86 L 6202.03,168.102 V 57.7695 h -72.84 V 171.328 l -104.71,200.184" /><path
                          id="path48"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 6319.45,157.32 0.43,1.289 h 70.89 c 0,-18.238 4.48,-31.211 13.44,-38.879 8.97,-7.691 22.7,-11.539 41.2,-11.539 15.19,0 26.51,3.02 33.96,9.059 7.46,6.031 11.18,13.93 11.18,23.699 0,11.199 -3.65,19.781 -10.97,25.75 -7.3,5.961 -20.55,12.313 -39.76,19.071 -38.9,12.64 -67.82,26.109 -86.77,40.402 -18.94,14.289 -28.42,34.879 -28.42,61.726 0,26.293 11,47.551 32.97,63.793 21.98,16.231 49.92,24.348 83.83,24.348 35.76,-0.148 64.56,-8.551 86.41,-25.219 21.11,-16.23 31.67,-38.859 31.67,-67.871 0,-1.008 0,-1.937 0,-2.801 l -0.43,-1.289 h -70.68 c 0,14.789 -4.01,25.532 -12.04,32.2 -8.03,6.679 -20.13,10.019 -36.33,10.019 -13.18,0 -23.57,-3.226 -31.17,-9.687 -7.6,-6.461 -11.39,-14.442 -11.39,-23.922 0,-9.188 3.9,-16.547 11.71,-22.078 7.82,-5.539 22.4,-12.469 43.75,-20.801 36.28,-10.77 63.76,-23.918 82.45,-39.43 18.67,-15.512 28,-36.769 28,-63.781 0,-27.578 -10.76,-49.1602 -32.28,-64.7501 -21.52,-15.5898 -50.08,-23.3789 -85.67,-23.3789 -35.43,0.1406 -65.51,8.5508 -90.22,25.2109 -23.98,16.2305 -35.91,41.5081 -35.76,75.8481 0,1 0,2.011 0,3.011" /><path
                          id="path50"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="m 6584.07,315.488 v 56.024 h 237.23 v -56.024 h -82.96 V 57.7695 h -72.83 V 315.488 h -81.44" /><path
                          id="path52"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="M 6850.83,57.7695 V 371.512 h 205.57 v -56.024 h -132.73 v -67.449 h 110.53 v -56.027 h -110.53 v -78.211 h 132.51 V 57.7695 h -205.35" /><path
                          id="path54"
                          style="fill:#58595b;fill-opacity:1;fill-rule:nonzero;stroke:none"
                          d="M 7097.12,57.7695 V 371.512 h 95.45 l 70.26,-219.582 h 1.29 L 7335,371.512 h 96.32 V 57.7695 h -72.83 V 259.891 l -1.29,0.218 -69.17,-202.3395 h -48.91 l -67.87,199.3205 -1.29,-0.211 V 57.7695 h -72.84" /></g></g>
                </svg>
        
                <div class="row">
                    <div class="column" style="font-size: 18px;">
                        <p><b class="half">ODIGA - Dig. Intelligence & Global Analytics, Lda.</b></p>
                        <p>Rua Professor Mira Fernandes 20/21 r/c loja</p>
                        <p>Lisboa</p>
                        <p>1900-381, Lisboa</p>
                        <p>Telef. 961 397 565</p>
                        <p>geral@diga.pt</p>
                        <p>www.diga.pt</p>
                        <p>Capital Social 3,00</p>
                        <p><b class="half">Contribuinte №514198460</b></p>
                        <p><b class="half">Original</b></p>
                    </div>
                    <div class="column" style="font-size: 20px;">
                        <p>Exmo.(s) Sr.(s)</p>
                        <p>'.$company_name.'</p>
                        <p>'.$postcode.', '.$country.', '.$city.',</p>
                        <p>'.$address.'</p>
                    </div>
                </div>
        
                <h2>Fatura FA '.Carbon::now()->year.'/'.env('DB_DATABASE', '958').'-'.$payment_id.'</h2>
                <hr style="border-width: 3px;" />
        
                <table style="width: 100%">
                    <tr>
                        <td valign="top">
                            <p><b class="half">V/№ Contrib.</b></p>
                            <p>7814179462</p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Requisição</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Moeda</b></p>
                            <p>EUR</p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Câmbio</b></p>
                            <p>1,00</p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Data</b></p>
                            <p>2019-08-04</p>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <p><b class="half">Desc. Cli.</b></p>
                            <p>0,00</p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Desc. Fin.</b></p>
                            <p>0,00</p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Vencimento</b></p>
                            <p>2019-08-04</p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Condição Pagamento</b></p>
                            <p>Pronto pagamento</p>
                        </td>
                    </tr>
                </table>
                <hr style="border-width: 2px;" />
                <table style="width: 100%">
                    <tr>
                        <td valign="top">
                            <p><b class="half">Artigo</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Descrição</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Qtd.</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Un.</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Pr. Unitârio</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Desc.</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">IVA</b></p>
                        </td>
                        <td valign="top">
                            <p><b class="half">Valor</b></p>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <p>DES. APL. EXP</p>
                        </td>
                        <td valign="top">
                            <p>'.$modules_text.'</p>
                        </td>
                        <td valign="top">
                            <p>'.$number_of_workers.',00</p>
                        </td>
                        <td valign="top">
                            <p>UN</p>
                        </td>
                        <td valign="top">
                            <p>'.$sum.',00</p>
                        </td>
                        <td valign="top">
                            <p>0,00 (03)</p>
                        </td>
                        <td valign="top">
                            <p>0,00</p>
                        </td>
                        <td valign="top">
                            <p>'.$sum.',00</p>
                        </td>
                    </tr>
                </table>
        
                <p>I3LX-Processado por Programa Certificado № 0030/AT / FA 2019/4 / © PRIMAVERA BSS /</p>
                <p>Nos termos do Art. 36°, Alinea f) do №5 do CIVA, os serviços constantes deste documento foram realizados na respectiva data de emissâo.</p>
                <hr style="border-width: 2px;" />
                <div class="row">
                    <div class="column-70">
                        <table style="width: 100%">
                            <tr>
                                <td>
                                    <p><b class="half">Taxa</b></p>
                                </td>
                                <td>
                                </td>
                                <td>
                                    <p><b class="half">Insidéncia</b></p>
                                </td>
                                <td>
                                    <p><b class="half">Total IVA</b></p>
                                </td>
                                <td>
                                    <p><b class="half">Motivo Isenção</b></p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p>0,00</p>
                                </td>
                                <td>
                                    <p>(03)</p>
                                </td>
                                <td>
                                    <p>'.$sum.',00</p>
                                </td>
                                <td>
                                    <p>0,00</p>
                                </td>
                                <td>
                                    <p>Isento-Ex.Art.14CIVA</p>
                                </td>
                            </tr>
                        </table>
        
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="132px" height="88px" viewBox="0 0 530 176" enable-background="new 0 0 530 176" xml:space="preserve">  <image id="image0" width="530" height="176" x="0" y="0"
                            xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAhIAAACwCAYAAABadodzAAAABGdBTUEAALGPC/xhBQAAACBjSFJN
                        AAB6JQAAgIMAAPn/AACA6QAAdTAAAOpgAAA6mAAAF2+SX8VGAAAABmJLR0QA/wD/AP+gvaeTAAAA
                        CXBIWXMAAC4jAAAuIwF4pT92AACAAElEQVR42uydd3hcxdXG3zO3bNGqN0uy3Huj2JQETCckAUwL
                        DuGjGELoptdgsAgdEhJCgEASMCUUOzQDIQnFgDHdNq64VxWra7X93pk53x93V5ZkG0OA0Pb3PHpW
                        2+6dvWXmnTOnEDMjS5YsWbJ895DpRyP9SJnuXqcfRc/PM3mPCgoAYHa9Y/T6YGaDvffopB996Xey
                        A8z3AfHFN5ElS5YsWbJk+b5ifvFNZMmSJUuW7xYZC4Tq9Zh+nXb2vSzfJ7JCIkuWLFmyfMnI7JLG
                        94iskMiSJUuW7yhdvhHo9U/vRW3q+fZWu4La5pXPtkf1GT+f5btAVkhkyZIlS5YvmewSx/eJrJDI
                        kiVLlu8otI1lYGc+Dp/p7SxZepAVElmyZMnyfWdHHg1kp//JLlVk2THZ8M8sWb4gzAylena0Silk
                        c7Rk+SZAZHZNGB3HQe//Hce7dqXrvT5xl8dD3b8vpYSUjIw1g6hLXewYxo7FSZbvHJTt7LJkyZLl
                        u4qCUgpCCBARpGSYptk1yO9bNi/wZsM+CcMATp30enHVwHAfO7/uMGJ/SjOi104/42HT8j5LYpmp
                        9ShJ5C18lJaWBpqbmxPb3e0OE1Zl+S6SFRJZsnwK3e+PTAfanYzlIfMeM0NrDcuyQERd3+/93R29
                        niXLl0r68lXasz4IkVEF1OP9S09ZUFEwcN5UDbmvEHofAeGAKeJy4v5UxH72xtsuXWBaAAg9rvcs
                        WYCskMiS5Svl0zrdbIec5auGpQIZRloAeAYC1oCrACutKc499/WhFaVrf8YUO0EQjZBS2pZhg4ik
                        y9EIM32glf/x6dMvfMS0PMsE82j5hRqW5TtF1kciS5YvADN3/fVGKfWpQiErIrJ81ZA2vLoaDECl
                        a2kYgLABRcAuRyJU1mfVGSyi01JOYly4vdOOhKOIdcZAmkwBQxpkjDPMxNU33vCHC1kDrEdLx1Eg
                        IvKcMLf3l+X7RFZIZMnyBXBdF1rrHssYGYTI3l5Zvma6pXPQGhAEyjhC/uqy+r5H7fHYr0DOL9vb
                        O4MlJX1QXFSG/LwiGMKGk1IAGYWazXWCrGEk+PSLz75vAABUVR0b4Kw5O0uabE+XJcvnIBOhoZSC
                        lBKWZcEwurzZ4TgOpJRdzzNkPi+l3CbCI0uWrxSzhgDASC9lgICfXVZf0jf/+eOJ2450nUSxkozO
                        cBQdHZ2wzNCCvJyS1wL+YHsqlTJty3bAupUgq3LLYj8kscHesuX5Xk6WvS0RWcvE94lsHoksWT4D
                        GSfKzCRMaw2fz0dHHHFE0YsvvhgBoKdMmdKnoKCgDICIx+Pt7e3t0ZkzZzZmlj4syyIAcF2Xs/4R
                        Wf5XPDAoEDgTiAOA64LBwNCchSOFdienUqm9AYFgMIhEIgHLslEQP/L8c8eWrLhnzasnp+zUWdIl
                        gAwbBJ9pyj3POu3fcw3jrM1ERMzZmhpZss6WWXZKJu58Rylvd5YKt/domUpfcL1C0XdwGSoFCAKo
                        226YAa0zIW0aAFADkwDgeMAYnZ4KLQOM0fDMA0oBpmlSxhzbZRRgIG1QgFKAkW7tUX0QGHnyo2Py
                        fP0rnfB+K+ySp38G6BxF7ceAjQYiOUIg+Lpipw8R5zBThEiOAHExs6gn78DENDgKFhGQziWYjQSj
                        Q7MsEGQ0swosEkZ0rJJ589zVk/99w+No7HKK6yp6oEBkktYuZ5ZPPAFiQCkFwzAgpfRC+tJIKUFE
                        3nvp16wDvQMuX4cjCNBgmCSIOXM+fL1VTXr+yg6yfINRO3junc/9cFruXMxJAusxdgnsBaMQnXp5
                        69B+uXOv0Og4SoA0iAub29bbRYUl6xOJulNvuv3WucAphR+3Cv8//vyrs4lbdufY5iN8Pp8Oavel
                        fLf8oTNmHvRvfFIYh+Xtj0ml9+7d19vU+Mjy1bLDhGJf1Cr02VKdZ4VElp3w9QqJrZvZ/stKee27
                        wfBRDVLcfUCcsQHWlAHSybSTmbHHkL8E56/bx2G91eucBOzzj28szKucX2HlNO5umPEDNdxxBK4Q
                        CNiA2AzSg5hFLZEeABYNIFXNbKwiUsO89os6gPNAsMCiGdB5AMz2cGtuTk4OUqkUiAimaUIphWAw
                        EGcWa8CoE8JIgKmdIRxmGVbKv0LGS5c50SEtG8M/bZo5c1W8x6FihtbUtaTCzBBCEABDSikzr3tL
                        Lya5vPXo9j5b1HV+s0Li28mnCwnCAOtXbxxf1RQ+PjLyqD2SLRehpLLob7fYShwO4oDQ/vdBulCh
                        o9NUpQ9cfv3PHjIMAwqHFRgop8k3PULD8Or1unP5+aZpotTy/ZWZccFvzjgTKXBWSHxDyAqJLN9s
                        eguJ/20xHupl/5dScmag7A4rz6JA3bx+WANeuBqI9ba32tTTn+5bWNo50vRHD9SQexNxOcEyATg5
                        OTk6Py8f4c44YrEYCJzLEO2ChGOaJogIlmUhGussIiDAoBhBFwOwAWoD4AfYBrRp2b46x3EEgQtB
                        yHVdJ2iYDCKSWot3iZADAMxIAKLNILMV0FAsCwVZ9d5xkMWsxdrGLcV/uef+E9eTAAGw3BScTMIg
                        AHBcBdveenxKb78yUHfFrQkBz3JDYJiTLROzAABqW9N0ttjSt5PeA4aZFhIPWQBwAKYg9Pt3c8d3
                        rPg1EDvehM/PoLDB5jKDrAgpfuOKv5/0CNbDJSJaEvtR/phgUF4+/1llv/zWhTL84S05wVB7mS/w
                        dCwZGXzpfecdjrx2wtq8OLB1HFPp6ycrJL4p7ExIGDv5fFZIZPlS+bzlhNPsUCnvbD/b7s8z65s9
                        tst6q3hQCjAtkEyBM1qDTJinTUTQl19rlQx/a5gVjBwEklUANIFzACEBDjBzXyI9BIRCAAKMGEBx
                        vz/QnkzFh3jPIRjUSOBChtgITyz4AWiAHID9nqhAAoDpiQ8lGQgTUA7iXAC24zhd1gQhrI8sy9KG
                        MJBMJf0E3R+EAJgT3mEKxpnNj4WgCIEMZh0hUEyxbNPK2Kgd3+p468D1d/xl4uaex2GZCYxWzIQa
                        ANeqFAshugxEBKSXRnZiGcqOBN8Stj9gODDgA+j4GxvLhooXjrFSqdO0dva0hC9CFHjZYCQMWfYf
                        u6H/Gxf9ZUw9DICIBDPEk/MHWvOxDvY/3zk71vzGnUWFxeEgnDqT7NVnvL7nKaG5u0XAioGskPjm
                        8nmFxH9HVkhk+ZLpdeHyDi7UbXqYHZnYdzBjZs/SMACw1suuL+Pc3RAq2wsldjCcB0oJLnhuGhgJ
                        BhOBDAYrEAJay3wQ5zC7eyul4LouLMuCZdoNDAoTuBjEhcwylnJS+bbtXwpQlLVYBdLVrMQKEshv
                        rR10g2E6ti8YKzbtZIUwZTkYWmujHWAhjFSpEw98lIr7W/058Qp/KHWCMORxAMUZtJ5ZJzyLBIcI
                        yPUi/DO/hwtSCV9uIBAAQYQB0gxXMkQzQ3YCHGSk4szULEBLZaLg5Rt+e/LbrMFdAouIlgHGrBkQ
                        2AO6ZrSUOzqeQDrPQLfnWX/Qbxs9Bfjg2xAcJREYxzOONrjzZHI1iFBokBjC2nzTB/+/+m84/YkT
                        HkbzawLiAKm0J3I9xf7Le7ikMvzMTSq87vRAIJgMsFvr45x/DF+R/MOgLZwY8ta5nQC6fHGMTD2O
                        r/swfG/YmVDY0QTwy7U8ZoVElk/nc+fM/3KFxMCBsNasSTmGYXdl5gPg2QBMmCwhlQKmX9g61C54
                        d4xhJUaAUuUEEQAApmixZfsSjpO0NbsDCFwKcAHAgiGavaZwsWZVKISRZKZVrI01Wvo+cqOVbyVp
                        hSkMHXz/tYr33l6yS4z1aIfEMpv1aAcASMBkjR6Ds+t6WQOV8hw5mQEpvddcF/jlSX8aGOu0k+F2
                        v/PaB6eEf3Hwa8UVw9YPDObFxxsmdoXQ5QDnEXGxSYVDAPYrpZBIJKDZQW5uXlKzqiVwPgMbAZgM
                        N0kQjQTRyKCIcux5NTec8Sz9szSASS1JpJ0qGQKDBln2hg1wpWQ2egWAZ4XEd4RuFrsLp24YXlL2
                        75tcGTlOCOvfoVDBuli0bZghrFZfR+ENV/zu/5bC2Orky8xA2olZLDPMac8++p4tO8d3dnaiT27o
                        PZtzXpl6X8ktaJiUgJku+JXebVZI/K/5koXEf2mRzAqJLJ/Oly0kdrqdXlU0pdEjqsK0PN+AS6e8
                        W+bLqysKFLRd4G3WVgBcwABDhTTLIkAVgNxcgAW8nCkmQ2qA0v0e26zFctbmWun6ljZtGDb3L0/u
                        tx4AyIANQGoF7UV8pBP6iK1CoXt0ReZ/rQHDrDFZ18jM5zNt7+3aMWHIVfkfrr41rLX33e75q446
                        9K/lQ0Z3jskPVR0GkCaRHEgitX8yFS2XUkIqF6lUCqYpUFBQAEFmHYNaGY70hBHVCgTestWGD8lJ
                        1dpNq9ddPOO51q4ORKNHFhnewXnJDgjfUjJCUIB+c93Dj8eSW06Ix+MoLspdIeCbw0i2uwnrnw/f
                        8dwHm/gNNxMBxAAEEUnNbIoDxMW/mbVHDj/9jq1SMYDiAeAZS/reu/Dmhkfh1vAOhUS2aNdXjNrJ
                        8wz/pYDIkBUSWb4UvqiQ2JkJbWeXnzcxwkAT9uSz5/b35dZVkxWtNgw1nEgN9Ns5McdNFSitSkCc
                        AxjpIVIXELgw5USLAcBn+xuZxSrNulFJ+714uHCeVpajVcz5w/0nLyUvxNQvXSR7DPjp362UlwvC
                        NLeOvt2FhJS66z2lGIZBYAZc14Ft2+nXFUzTFEqxFts5nl5Y61bBwQwIAzZLOGTCnnbpS0cYvsY9
                        hZk8rK2tZdeWlhZoLWHbNizLQn5+PvLzCtoBOCDkgpEQiEhmrDJgzjOTvtfDn1QsuPG541u7jn1m
                        X1kh8e1mB/fRHde+dL5r1t7W1t4UbGtrQ3FZLvJC5de31zf97c6/XL+594yViIXUpAUBgmBOu+GR
                        h6OdDScWBf0Q8D/v51RjKFYy46xbT3wXBMDIComvh6yQyPIdJhNlkYkGuOXlA/NSP5kbn45Uuq8x
                        oFgB2u4xQy8rRaCpCT2y5Z1++pK+A/N8RSK4ahib8YEgt0iQDDC0IHIrFadGAm4Og1oJeihAbQyd
                        AgBW4h0QhJJinVbGxkRnztIPnp608u3GkojX0J39kMw/+ks+QjtIJvsZb0MSEKcd9XxVaf+OYaYv
                        WeULOFcT6RGRSAyRSASRSAyWZSE3lAciQn6OgWAwKFOuY1qm7yMD1gozUf7Ipbf87BUyvWgW1t6v
                        NAzgytmlAWvt3sEzLn4x8urSg4NnjHm1I7Nvx/GWnzLCKMvXQc8Bg8jLjUK0zJTuaNl1T2nginNe
                        GVjQZ/NDK1cv2T+U50NBQQF8fvFBoGPwlMvvOvoTpSQMw2/e94cfhc6+6N8dACBojqX5QBcMXH3J
                        zF2s/Oa/xeOd43P9JgwKPBZy1DMVnwyYe8LTR7UAABuqR6uyQuKrYXtViD3n80z/pFCzzGfiQ88X
                        CqOhapBiZtEj8Z3jqB73L3eLdMvk7AEAzen/hTe5ySTk8/lMc9Cg4+2qqpBp24Z47bW/RgGorJDI
                        8oWQ3XwhTXPrBevNxhVutr0XO+46OF8LR/9+6uudXuSi1+GccvIbFY88ckADNLDHMAQPO+7f+1j+
                        pgkCRhDkVpiw4wCgoVwid7Dm2A+lcktNw2hniI0MVzJTKyhVrjXWsFZrpYtVt97hPKlVTTyzpNB9
                        rV9r7w/wwkO3z5ctIDJ8TiFBmWyaPTsErQDD9DLTnnvSvwcUlsZHW8HWnwozdXpdXZ0Zi8aRSCQw
                        uF8ltNbwBwMIBkJrpHKGmIYVZvbfrVf86k/TnkIjAEjFME0Cp31VNsJnr8Eg8xCsjW+3uV7uCps5
                        m2fif8sOLH7p6+eoMgSeqUPi2CoExp/zwCVtW+puXLduHXx+gQkTJmhXJS/Em/0fvubNYyPe9eRl
                        MmOwFEQ0HGx8oiGvO2ftALP8lQukkTiJwLmGwjriwPOFUX526u/PXQQJB2YNMV/L3VuVFRJfLt3T
                        6W8v7J2I0snqPj1JdcbamUnAxwCGDJkcXLtmZvzTvkdiYFp1bOjVIQ5Id2Qb3FRKclZIZPlSyORx
                        MKyeg/Zt8wcHE+PXyelIpQccG66SEDA9iwQDQoCm/fqZX5hmeHeCFfQ+J2zNqb4myVKQ3DUWjwrT
                        NGHbQoKpUTEvhxabmd2EdM3Fcbd+8+zZ899Zu/aVsLfem2mEd7174kGDWXUlhvLQvR5782WVo/lv
                        t0M2ALyRbuABcNOWna1hsOliTACA849f37946JtHG774lHC4dde1K1Zh+PDhALwEVQUFBQgGc8LR
                        ZCRfCGsNp4xLlt527sszNeRRZVcG9vnbg7hiUl1iCSaEmmJl5kTrnx0+n88EoFzXZcMwoLVOZxXN
                        jhL/e3oKCWYDQpAJsGINVq43UNRc+doPjZxlz6xYtLA8mUyisKQAIwePnjF+jr7ggIMuSKCmp8kt
                        BdZ+YtKaWAiI6dc+dj6o9deKXJNgLhcsP1aO/c6wtYVzTn785MZMO9js2aouIfFfRotn6QkRmVKm
                        ZO8w7cy4vaN7UClPhNjWjk9ARqOYFmifktn+eS2DXTc1Wu498oHg0LHRoGqznFlz70ymRYRKpSRv
                        rxAhEWWXNrJ8MTxTmdFjRq2U5y1uGAAMBQ2G6FbWpWzf2YEjh1klPl/KX1LU8UNABQyTRglS/bQm
                        P5GqJqAKxLmCqdELc+Q2ra2FWor3ZLzko0/e67P4mXf3bCWjxq9VTVIzd4kHL2W0AJEhpNSaiLqJ
                        m9434LdDSACQbwDiQILGARDOfxxJRDANEyQm28B0sB7tZM7DyT/9pKJq5Ie7FuamLky6kcNisRhS
                        qRQ6OzsxdOhQsMFIJpMIh9tQVtpnASO6+KN7I+c+/0kx7ruvduA51760wdtST4tE2s/DdF1Xdk/L
                        neV/RU8hIaXwLIGcjiByIadNXT/ULvvPYwsXfrCn6myBZVn4wT57Ov1SZUdO9q1/HcePBkb/HGtn
                        gAZM0a5UChAmDPJcHm687qljlBGeKoQ70UX0fRj2AsSNd1KxvMVXPHHE+pKGksRW35odLG1khcSX
                        ArPsSom/9bVufRh7fW1mKYLE1ucZuhsySMDepwTGmw1ICAIuPf/2obDINgHevMloevL5S1qBGdb4
                        QY753idnxk17R+3q+TwrJLLshE/PbMnpDkOnL2jAu5gzF68k770J5yG0b9FTVfl2ZJxluCOhhB+g
                        GHTyMACaiEpBuhqacuPxOHJycqC1/QI77odOIvTevL9Pee/tRkS6m0qZe2ayTLcEPQtibX8AzxTg
                        2mq52JGQ+LoGS689M94w/AMOgDwATtoSkWlvr3alb+PMLKP7Wvl1F83cRwQ6Bxv++M/qG2qPNE0T
                        Qgj4/X6UlxTrjRs3CttnoDC/5HmrI3nN5adevR7j5+PcI04Tdz+/MApgGwtEtujY18UOhET65Sv7
                        IJB77l/uXb5i0ZTa2lqU5igMGDAAg/tUXHHuP6++q2PvveyCH/WRmPRsUikJGDYEyKvVycDVV7y6
                        V07O6gsspn0Yolka7YshrA1um/uv235/xEJOpVPLp5cEZVox6HS/YPduZlZIfEF6W6B6WQK7CQlj
                        B8c6He1mnnLE6/nFA9f2MQwOCJ10mEm2b+xfD1+Tj30RseJfgzvebp6UICIaNOj4wCefPBHvniW3
                        Zzu8x0xTskIiy07YSYrs9OUj0ybVzCWuFHDUBIRoH/hGl724l200/4BI9gU7hWBZDcJgJWW+YRoN
                        ADRgaDDaNdPHDEoCQNLJfeV3t5z6DGvWXgTDWUFglcN6juwtKJgzAoLR3fym1LZOSj0fMwLiy7I8
                        9OK/Xivu3a6dtzNTZdSzwHg7rKSzgvX6/jg0MMGcH/zxWRtG2cUNpzgqNtXv92sTJpRSwrSAzZs3
                        o6KyDD5Z+H+j545/8Sev79aZ0SuO48Dn85HWmjPbzoQMZvlfsu3Sxi67HBFavPDFKADcUPPIJS3t
                        q3+3YP4yFBYWorLIxfBBg56/uHbjyei3Pokp3vcuO2mp8du3G5Ocvp8Fwb78qicOCPic44HEngYH
                        kgI5i1PG+hY2rFpno/vP2++/fD10ugkWAM2QhnddZoXEV4XqcnQEsE0mWiLYAFyWW23CR/VBYOgR
                        q4rJjJp2sCnPsCMFIG0oNxiORaNt898f0/LGgrFRQcDRh/21oCBgGx2JdjX7tUOizKN75MTpLRh2
                        9HpWSGTZCZlLJVNsq6eg2Ld0dmDulkmJjGWAFXDVBS8M9+c37CZMt5/EUClEx+5AdBdAl4Jcmwg+
                        ZlYAkiDVrNh6x3HteY7yNyxtHfxxRW5jbp9Q/YRIKmdNKnDK0odqnGS39oDhJZ42DANEvSwkXcLG
                        G1B35Ey5oxvkS+czJ3jR23/OWy0PWm+7HSE8AXX69aZ/BuBwjff6G4D45PGxueeuWhLmGkdnppBE
                        sFnBmXbuKxN8ZatrBOvDDWGvWrd21bCioiIo7aK1tRWjho94XmhryYzn7r5t8eLF0cxuu4sHx3Gy
                        ERz/c7Z1tpSSYRJh+rQHTlDofOLtt99GPOaioqICuw4LYnRR7pTJ/Tc/jUiuD4e/1AkA3H+NS0SQ
                        MGBN+Mi+5NCNewfsLScYZP2AmWKEgmXM/jr2r0wktG/e766d+wHrmbLrsmQFIpEVEl85Dmrgo+sJ
                        6O5UKSXDsoTJ01nR9cClU1ZV2bkb8w07kQcwcXxYa+fSkQ0bP4E77OiV5cJq8KlEZaxt7bDOh+Yi
                        oiU4U5vIMAClM/e2NyGxLItSqRRnhEu37Po96OpHs0Iiy6fz6UKiy6TuAtde9vwoO7d1X8NMjQfp
                        YoBJcmULEJmgdXx313XhD1hxZnOpVNb7SuV8oimZaO4c9d6Lm8bXHdjvhZLH/3jkeiLgwNMfKp3z
                        4GnNBlwAwOQa254FSK5xNQAwe7NuZsqs2/csv809Hnr8kgw9Pv9V8QWFhJImiND11307nie2mx7Y
                        vZ571jLbbl8Hs3AQ5PGjIQFHA8DgwSPy165dG4b2Ij6EAMiE+O2Fcw+VeasvdGX0J+FwGP6Ajba2
                        NnS0d2KXXXZBVNe/ppR64sknn3xi7dq18YwXedYS8XWx/fDP845eW1K2y7OvvTPvw3GxWAzBQD7y
                        8/NxwG4lc87zfXI4xgNYV+jDj/4Tw1v7+XDKQ9HL9i333zS3JXnF1Q/uFrJxmCESBxHMChL+lVoW
                        LWQ2Ejqwqulm56KZPB0OickmuzMlCFBKwrDM7NLGV45nEd66pGFAKYVfHPhMXtGQtvyiIvR1Fbki
                        OTLsRgdGhSkMZTf4gtxPU0Np543PoXWgCXtcCYzZLXAnlXgziqfrkMj4U5gW4J3THS+P7IiskPjW
                        sIOlhc+aQORzlufeuvatQGTSYh6bAwBPY4kDwJ0OyYoVjIwPIAO//OX71f0qOiYKo30MIMeRSB6a
                        TLq23xdsV2h6C1ADARRrTe+6KfWSdNGcTDiNd/5x+keW3bvBvQfUr2jJ4fvCNksr2x5fIjKn/fq2
                        3ximPjHcnujf1tYGQT7U1dVhn713g2374qlozqFuZ37DTfcfv36gONBez2+4v3/kiKL9jlyeAIAX
                        CtclABjTWEqDDCgAw2+bH1x75fj4Z2tolu0xeeML5XP6H9nZjHSOlclkonCQjfvXJe57fGzOfYcv
                        RkM+1Hk3v/fY0sWvHdu48g3k5uYiTyzFAQccAAzI3+9XU95cuGzZh8n31xabv5rUItUywBgD4Hjg
                        zv7r9kjkvHsGOD4KLJoDATORTKUMsGwCG41XXPfL6wGkrX/fRp+Yz5q4qTdeqn7GZxsge/smZZwf
                        N6adpWfAmxG9OQNW6R7QT3XVvMnk1TG2O4hncvBeWYFAzgnLB8asj/obhsrXrtnIqQG1rrXChutz
                        3GRnYtNmFZ392h/iXkimN/H7X1kMs27X33McR8GyjK7ZrhCCXNdl0yQwS27HMA0AwZYSI17S4rpS
                        wGf9wtR6pjzmmOdLSu2jjOoRS84FsBvISAJUAUD7fD5oloUM7Zeu+IeTEotjEWv9fX9tX8q65qtK
                        0pDlc5LO2CmJMO2cM+56sqys4OyioqJzXAcoLCzEgvmLcfSk41bV8cZ7TMv/GxKTN/PSOfqOe88c
                        cPm5f9mA9JrJC+ntac0ggwEQVmZFxBdmRf8BsTy0k+PkeffpLChgXQL3A+ecuCS28HEMCEaQH4+u
                        P7axsRGBQACO46C4qhgjB/aZs/+pSxYCVRg9+n0AsySWeVORZ4fDXF25fkgq8Ma1YNlfgKKVlX0T
                        zS0bAEiltFwT/fO599H0zzrwfr/RzPBZglKuFy+x1Ym7J69PSfXIu6JYwRIm6cyM3guHNydOXBgc
                        PHhdXlVhcDRImdYpSUg2XVL+iJaBJp3s0xZp2KOzw2ijeDQk69Ynnfnr9nGkvFB6LmL/WwNB1iLx
                        jeczWiS2uW7/O9ti7zXwkT+cEVw3/8zE9One+xs3Pl1VXr5lnGH4i0wzuasgMZBZ9gUoBHChICNf
                        a/N95ea/rJXV3tm58eO777v4I8CrUQF4dSp23O6sReJLZScWiUzWOtO8QfD0GhzxDPLHH3n3TYZp
                        7EPQVa6TKm5vb0dZedE8Zg0g9dIHz9Xd/eLi+2I1AGrS28k8XgvNzBoGZYs3fRk4AHwA8dYjaQEl
                        BtGHCtMHuNfZL94D1X7gO+/MHaG1RoFog8/nw2E/HBY9tZ8+Eifc+LZCugLNsq29wU2PvXqIstae
                        C6AIoGR5STGaW5pNIVQjs1GbaMKfb/zT2atZfNeicz6fMNrx6JhxUqWtIiCN7vYtzoSy6XR59W51
                        gyr2nx1oentSwpWAkV5amDz5w5Lq6tXlQsQLDMMN+eWIDoNc0+GoFFweSXUE2hMdZU59YqUCgKf/
                        eXAHia2hn1/XDZcVEt94vqiQ+GzV35TyvIMNMrtCKknMsKdjins9gMsufn1CTrB2TxiJ3ZjdvgA5
                        ANtEak+AOgEd0ZpWKTf39Uhn6fzl8w9Y/cq7ua3UK7qCutb204WqttE5WSHxpfIZhIQX2lljA9Cs
                        aiQZsK+86JGD/bnt51nkK9bs7E2kGwFoRiLI0r5k+s2XPcbMTiYlumSXMxlLNRhKApZJWSHxBSGC
                        CUAtZTJGA2rWmom5ewyZm8QMxh/mrazMr3znjUWL5vePdDQgGAyis2UdJk6ciD1sccRRa1teP2g+
                        MGddhct8PQBg+oUfDRV5S/Yjcn4CkiMNylli2zZSqeaBzKKRyF3z2m8uuWyOhuPlTd9x274PIcC8
                        kxoW3jLv9g+SYtWVV0Okbw6pIQWhK5GcacKcMuWNPqFQa7EQ0XzL0HlgoZQKtYNzEgZ1WhbnptxY
                        qKNtzcRwtA4yngPR0QA1rwVJ1r1GgqyQyLJ9Pq9lobeA8PVaJMuY1jJFejxnrcy70umZNrrm8sQP
                        hLmpRPgWHacRH8twQNDlGq4NQAKyXSvzPVdhhZRm3YoV9Obs2WfXZZx3TAGQqBEABOuartCiLkfH
                        nTofZoXEF2KHQqLn8c0Iim5VI81zT5o1oHJg+F6ApKNaf2KavhWNTRtHlJeVhxU5/66pufbnmetM
                        gdE99bkgIsmajayU+GKkb+OatFPS/FVn5744bEUEM+bgurpn7930yYe/XLNmDfr396O9vR0mx3Hw
                        fvs+fMHz158JAPgofaKXAZfd83b/3NI1v2B0/p8BUVhW1v/V5qZ6aLiDTdBarX0Lpv3mjDszu2al
                        QOaO+52skPDQYJgkCANgDSo+01z14f3xTKZZQSDda7CfNClRPHzgi8NNO1INuEENUtB22JXBFkJx
                        0uDChN8aE+tTbWPNugdCTioUb14xsnXW3N0imZ1LF7J3ccHuQ/n/+rRkhcQ3nq9WSGTKfJPwCjhl
                        PnXVpY/uEQyFfwrKPQlsbgalqj0Bkcp1XRemZX2spP8lQMZIOCMcN/nBbbft8TfNBySl9KIChOiW
                        cx+ZfA/e/xnLhNjhz8oKiS+FnQiJffetyH377cYIILqqk7L27LFSAjeenhpi9vvHFDIjPyEiaI4X
                        N2yp719SXvhmuKFwyh/+cs6GzG66m3ldJWEZZlZGfFFKEUAdEjctOrrE2mNe7A40AwBOuzm5d1DP
                        /Nec2S/YRUVF6N+XsXr1ahxw0H7OgeEtwyfU3l8LAItnAeOmA9PDH41G7vs3CDKrA4FQQzIegSCz
                        TbMTIg7NU6n4/Jpbzn7dW6Qn/OmmyeUHylmto2u4V16BT0/N/F3jswiJHUU77DLx8dCxb58Yux7A
                        KUegqGzAgnJJjXkGJQPCCAchVFDGRn3MZjiY+Q4J0wITw+3TqWWp+4cHyjZ0T/TX1a7er33NpyMr
                        JL717MwruUtIpD0UZI8wTqINNusBDjRwxG4Ijf/pzP0MOzxBCL2LZneQQI4JqCqGLCSIdobUAMW1
                        xhwt7RUaEWhl1D39UOLZFU1XRBgM13Vh22Z6+webWs2R2/Q71LP09rZkhcSXwk4tEsCBp1n+V/6S
                        Snopr73j7ToMyyKwAo6uQO74c+99g8CF/oBZu2796ol9KkqSYOtvtZ/4bvjLU+c3gnrmldheat8s
                        /wVp2e/YDqpaqgIjS5qtvZ7/eNfg0qXPLV60sDCkksjJyYGt69HS0oI9f7jnfRcdO/6qO685CmYM
                        um1UfaUofPVUIjVEaZnv9/m060owJ6sF/B9pVuuTHaP+cfPd+3xyIGDNmU7uA4FBgTOvXJcWEFuL
                        sm2vAuU3nx0UOfuM7Gx07IrWSHdTQsAeOxb2+PFvFOTmru4X8NOurCkFiHT/SxLajmklOsFGKpEc
                        2gAAdU1lkSVLKuNjBsL2OdCz5iIJwGUGO46Cz2eSlJK/qWHXWSHxrefz1qXv5WORFhC7/ej5cWag
                        aR9TGOMUO6UgVBOpYbFo3ASAYE5oFWv7AxKJYWDRGm3Pv+nW3//fPJBnedhWGGivuBOZXZYIIbb6
                        Rxg7jRfKCokvhc8gJDxE2k+G0jk50jNPTSAT4rxjO6rLxjz+DFjHiPSguoaNVRUVVStkynfpG//c
                        7625i8ZGWQPU5UzmpJ12v5kd37cG6QAnHpLnzHyrc8J9CBWcA7H/n988+/0XnrgtlUphVJ8Qmpub
                        kWPHMGjQIAwMjDz0lB+e/cGdMaB20dKRhb75Fyl2yosKS5rD4Q5odoYAcIi1Mij40Qf3nnr1cw1I
                        CIuINTPI0w0LFx6St9tuc5MZIdE9u+K3q2DblywktjNcui7g88E844z3++YGVw8SQvkA0qaRLGMy
                        gqwCW7TMa1ZcEDMRdOOpokRb04BI7TokB/WHryMB9fS/0LF9jeDpue7VPZVSXedgR8tL/7OEe2my
                        QuJbS+9UuQwhLJq+FEbN6JQEgJplPrNmNEsiMll7JsqMGU65gOkDXXv5k4ea/sjeQpijtZaFgvS4
                        eCJa7roKPp8Php14UytaqjXVgSmaiMvFv787NY91jexh0vu29Cvfez5nkTK19fm0y586yg61XAjS
                        FZpTI5gZpmE93dYw8srf//mwtfuWzQ7MazkqqbXm6x8Wds2UlNMtRVGW7bC9gaDH8kH74CDeGht0
                        Jj3X4vs5zGsnLppOyTVTX3/5qfxAIIBSMwqfz4e+JRUYXzzgV0ftMXUmAFyx+PV9fEbtz+ykkIDK
                        U+zkA2yDOApYm1TK/8qNt534Mmv0WLrY2phBAe9x7bc6hLcGZKJbZ3k9gOOXeWpi1mgoDcm6y78H
                        6LFk0dNZ0tQaMi0abK3hHHooCsaOfLs/iS3FpuEUep8zTQ0ttPJ3kOq3JYXFQcCSzLnRZLI81tws
                        okuW7BFftw4SXsmftFLYfvtpG2f73nwzhHpWSHxr8e6NgQNNe82alLPV5LVtLQwSsFmnr0jt5WLf
                        9Yy//8iwIxPBvDcAgEQOQfV3XbfQtn0O2P6YtbnJpTVWPB5/5Oabb37GM1tnLQXfbj6fkNBSeL4u
                        BsR4wH/Erx+oEWbqcNd1Rmmt4fOZrVoWXDFt+mkPZjLkAUDNDLIDziDzyjO/3QPRV82OhETXa0vG
                        hdA3pg+5Yq29a5/WkTn+dx547905Y3xGOxKJBIrtJAYOHIj+hdVzzz72kl/+62Fged4jl7T5UyBW
                        RVYqrwHQNpFTrrQsBlnzVy467ZYnLkfs9KthPfgmOnvs/LsnJLofXGP5MuCp9EQr/RKAdD6Hn/tM
                        zIJyteSuSAy1NbkUAEw5GsVWAezcwpf7W3b9aMOQJaysJukWrU0kdt1cUTSAmzo3+ADASQ6Is/WQ
                        rbVfhcOhxIoVA2JLluQ6KXeAk+muxU4mYFkhkeUrxhMSUjK2V85ZScC0lpkstxZhufjc+YPzilaM
                        EYbT1zT0vgqpUQCbSjmjTNN0tLJfDOUUdkRjkSDAJmujubTyxdvOO++ZzelNiJqzYMOBrnnITde/
                        yBTZ2IGwyFoqvqHseImjO8wCUgKZS+ykn33Sf+iYV+YYEEVKy3xhcBwc+LOur7y75s+TNnR9nYjm
                        zS7x7zOpOfF1/9JvM6+/fmTxQQe9GDlwIGP/U178QzzVfM67776LvGAHhBDoX56D/tXV7/Uxx7wg
                        XRcNFD8SLMKJYLwFZDQZsWAHidRorVO5UAX/qLnxpAcBYGLZvMDbTfsktt6fmUm7mXklHbvFzudp
                        7zcNApHuSu8PCPjogBmw3ngY2GfPEuPN25oSYjudlEoLCJ8F+7LzFwxJOHkp235/KIxUHuuSOsvu
                        rATcgHRzaw3u056M7NkMABRYkeM3pdHWvnv73x5DK7ABwACdclkaBm1HOHz60jTtVChkhUSWL0TP
                        aA5WgDBBbgqcCd9UCph+DIpXvYjw0DPD/YN9nr1YIb4XWDSDuJCgBoM4n1msAYtGAGBN651o+ZO/
                        uf2YV7wlbjLfeANmc/Mg36hR6xKjRzvemqlmXH3OyPxb718d9hqQFRLfLnZkWeopLBgCrsuwTPJC
                        dgFMn37PU6bAOO8T5Aj4F8iYfqDm1jPeVdrTrYZtEZbBwOieXv9ZPh+Tl5E5fTQw85r3Tm8Lv3V/
                        KqIRj8cRi69CQUEBRg4pbx02eMgHa9Z05BFEpxR5cQAqaoTXgFmR64sSqcKUkzP3xhtP/qdhpH2U
                        DKC09KhAc/MzOxB6mf7l27009Wmjm2bAFMtMV46WmboTw/d6Nbj/hLXFIZvLgHiu4JxqYbqFWvob
                        BHwuw4iDylsMJPyApQDXiEZ/uHnpC0UtlSNhlQ928pIx2/1kDjrnbkFCi51ZHbJCIss3BM+RcYYN
                        AFpOcUgA0ACZoOlTUGVVzzoZFNtLswoxpfIJejCTk8ssPtaSPiRDj9LaWd/Rat/3x3sv/oA5nQPC
                        BEDu1p0AABFqyBY17KZHnJ1ZJLIJpr5d9EpYBQaBsMvE2wsXzb2qXbrA+ec/tntVn+jDYHMtyOkP
                        pggUPbz05rMfnqkhoRkwBM26e2Lu8VPf6vwvG5IFAAH2OX9fPJDefnbFypUr0a+0Cjk5OSCKwrZt
                        FJcVQLDxsr+wFO3t7TbDsg1hNWkdLYAKPZHSidhZZ13wDAYAp0ye5Z87a1Zc80xJ6fLUtM39aezk
                        +bcLIhCOT/+IWVAugzUDVVchMDIRtQYXrc8LyNoc040WswoXgLUfcAWgAgY4aCihWCEJBDs6OybN
                        dztKnaKBzw+ULF1v+53+9vaBmx566IAGIk+4fJbAioxrhNjhTMsTFIQdLFl/w8gKiW853tIG9YjC
                        OP1A5PXb+58TLLtjJBnYF7DrQPFqqSOTGRIE/0uaU2EQlyuV+qCt2Z55z/0XfQwAmrWXmAgaRIbN
                        rLYxbWqlwJpgmOZWoZAVEt9OdpIhlUBCw9GuK2BZBpCOwLlh+oMPAzpEIjWCoKsFqf+k2vpP/81d
                        xyzzvkhZi8QXIBMlsZthhI757aNvfvzmnN1LS0tRlZOHoqIiNLW2IBgMQvsDLxhkhpOUrDatwEo4
                        rs0Q4fxU9SsldYcv+L9H0VC27+zAh29PUgAw8EDvBPOcTDh4qucVwL0sEN9yiyJNholZUN2il4xf
                        Xb662sfvDYYOlwI2A8o0IHJIwGKdm1QcagH7YgBgqtaUwYGkQ43WE0+cvaClBWrq1EdHb9lSUD9/
                        /r6RlasK493TXlsmkdTMgnr6VmzLzlJ1Z4TEt8MilC3a9S2ne3GYCUPag4cd+e6wfhPrxpumPlDB
                        HUmct5koOZG1ThH8rwCpOLOOKWkuWDnvR4/MfHNIs9bA6J+TvWym6wiyxIOzS3IOmtTiMkOzFNAa
                        GOKb4d+AAZL1AVIIsVUP9BYQtLN6XFlh8b/lczrHbkdYjIFtL7XYEUS21uxoDTiRfo/78tZfCLY3
                        M6VGMfPu/tzILiRoOWtmABZGf92//duLUgqWZZnX/nXd1LffWbB7/9IgKvsWwuqMwCAbprZRGChp
                        bWXDBPxJuyT1vuukCsDx1gXJn9zywqxhCQCQ7KDp7cMTV80uDXRsyaPpc7wqorNfOzi/qKxJ7jvW
                        GzC35pdJk7lsvtkT4Z3iPgWJp4CKfRHYezCCleeFC3xqcyWz3cdEsAwwwGR3KifUBgSSROVRwuCO
                        nJxg3DSBcPiPlYoCQkqzpaUFSjMcpU5e2CUeWEFDQMCFYQDTGbgh4+BJsKbv0MfkMy5ZbBO+vTO+
                        WLjrf0tWSHzLIXj5+H990QvjjvxF4w8Mw95VM6o0UAiYJsgdKsi/IhC0EY11FmplLW9cfOw99z5f
                        XEcESKVhGALLZrqOZo2/vgj/mZNaYgDMl5aMzfnJCDiGCaxXUzznyu7ZKbMa4HsDg6GZHdZePpDV
                        Lxzy3ugT/3om2OgQpuOAEQS0mRYRWD/74ODAqvUOxuNb7az3daG1RklJiUWRZWfV19ejCDa2kEBq
                        yxYopTB+9x/BZHupQb41YLvVdVuCjp3zakuy8MNnbxzWmliKksDz6DTIq31y+6SWpEYdX++Vx7Ym
                        Hfzatzoa47MyfA8Ef3LAysEnTFgw3DRi1QAbrEVEcU6nRP56iKBLVNEGDI0QddqAsgApolGYAPiP
                        d18wJxPmft99IFd6/kJDJpwVXPn+/fFMtNzPl/lMABg1GiJdKVmhKwngd5/s0sZXjNxBiuuuZ12u
                        B+nPLTPNDR9C5B81yCwsXCcJcM998qjiu054ugUArBrDdGsguy9lXHXtih/kWPOvJBISMBRz7AfM
                        9tK+ffsnmJehtrYWRGQkEomZN9988+NCCJimaTJnzc5ZPh3NDgQJACaUYihFsCwvg9+V02acY+nk
                        QGG4uxqUHME655ZYY93s2++/af3b540L7XvPR1HQt8M0u0N2uPSzk5lf5nsuAB9sSE9QDTTJXq/Z
                        YfLSiFf97ppAwxW3Jawa2G4NnN3PRWjBvYhecO5Tw0tL6y9cOX/JOdFoFIlkBGVlZUg4SfTt2xfV
                        ffs+r7SCJHeDlrSkLVL/7m9/+9vllp3Oe5COvmB80Q7+s1bL3P7neKemedXV3u75HdLFhjGtV/ut
                        mhm2WzOlhzjV7NnbrEGwzzgKlX5alw+sLDfFlnGWi1YItgAFViLCQLtBuREFX8qE0CmsFzJStaVx
                        bX1nXn+Z05lapx977A+Ntm18L2qJfFlkhcRXzM6EROYyzYzpRFaP8Kupjx5f+KeTZzUBwKAHxgfW
                        nflRApNhTAwhOKb0k4qSwKJTBaWGs9b9iOSYVMrxW1bBvysr+7U3NKwbwby2YfHixWc999xzm4nI
                        HDRoUM7atWvD3r7IZv52h3dl+WphuPBqeHoZSjOzM1cB10z/+0kBCh8sDDlBIFUFDt6RCre+dNMf
                        rlvcclVpoOTWukRWSKQfMyWeM77J1HMrw89CcOX9iIOBX12Ovv2C9z74/vtzD12/bCn2228/tLe3
                        AwCqB/RDZVm/v7scKzPY92pbS/LZzlqr7d7ZF7USERgKihVOXO4znxqdkl98jf2rFRJicjpl40yo
                        7mGaGcvJtPTSgMa2i3MHXYC8uS8gedpxW8pDuqGM9YqBlhkdC7DJbLUDgO0E1gFWksiMGsiLK0Q1
                        IxEgoz0HpO3W2r4f/nXWka3Shdy/YnZgXsuFSsr1zjc0E/U3lqyQ+IrhnXQ4W/Wu6qWAVfr7Nvaf
                        fHfeQTOnRgDgegImTkTuAfs9coowErsKGD6mxLHMiPl9ue8EgzlobWsuYua61Ut+fMUTzw3eDHh1
                        EHw+X9YKkeVz4l0uzCaItpZ/1wAuverZQ/PshpOEkBMFHBscuC8VaX35pjuvW9BVJOK7IiR2GP/f
                        e6LQ8/6uIaIazQxXep53pjfTHfLzPYJrZn4UFwRyGWwwcMopyyrKC9v6WcGFlzY11h2/dOlSDO1b
                        hlgsBiEE8vLyMGLkyAU2B/+hI/rfl/72nAXoVbRJpucFmYRKOw8f/KJ8utD4LEWvtv9869atyd4S
                        vDsTUjNw0nUby/s48wewai8Eo9AUib6kKcBsJkCBhORACxDqAJSZ75ZvNmHoFDrAFLMZ7SaxL9nS
                        IZr+9tjxrVrC6bFEmzVA/FdkfSS+NtJCgUVXcaNMRUwAUK4By+eVoJ3zxNTOA8YhxA1Q066aebhh
                        RXbX2qon0oWaY4eCqZFZrEskO4riyZa2jo6ca++669w3pdyaJc+2bWRERFpUZK0RWT4TnDaQ97by
                        OtIfgc1+gEMMtBO0Xxjat93p4/eUGqm9O9oyPRs8vICWNTM/igNASoOHn4XgT7CmorT43ycZoD1X
                        L//kp5s21SMUykVnpA2BQACxWAyjRk5YISTd36e+5J+/uP+42mUmzDEgJVOaWSgQEcgQnxJS+F+w
                        s3kmfbpQ2Qizh5Ls31V9OIP3fZ22diHtgwOkS9unv31EMfKnXvlukcW1FZW6cZggrgBpWyO4mdmI
                        Ku1vYQqEoSwmKuhI8MHrjzwJrfMeeWK06/ZtXf+fg+pmrkIcSPexBPz1kfRP1N7+s1aI/56skPiK
                        2TojyCjzzH3kI8BzWNQseZulDwvQGkyu986hh6wfaebNvYqhmdlqZ6PjdDBQWFT2VktrC4AYWhtx
                        xZ/uv2oBkeH/4x/PgmUBSjEyDkGu60IIkREVTnYNMMvnJSMo2Ot4DSH0AECXAggTcRkJw0/mZJMx
                        S7VcUeIvue1bntlyh0sZn9Hk3y2qikxhluxzhdX09m0JMLDLLktC48ej4PDcp2+3RE4xQI6GWdUe
                        jsK2gujfbzAikVXIK8zD6HGjYcf9Z2Ghf/Ev/n1cBwCMlpAsMk5WZq/WSZhkEX/VJuedbL2EJvoB
                        wEWdBIDr4XMBYHo6SqRs39mBhjcnJQwBCGPr8d5vN4RGT2grzM3ZXHBpn+ZcyU3llIqXCUpaDLYJ
                        iEL52wkFGwFDmciPgftEDUgbpEyV7KDHLimgJ17/xXuCtjqGdy8sl0GYoFRKsTAMELoVFswKi89M
                        Vkh8zUh2u27FjNOQ98RT6LeehHIx7MWfmXntBwYCAY7E2wYTyTGszVeYqbW5ddNJ61fkDfrb369c
                        b9um+NP9V4FZJQEFqrEF17CW0jNPW5aX8lJrrzLn9lJrZ8nSGwJ1dfCZYUlrQFDKD3BB+mOdANvK
                        ke2sZ0rAQQnw7RYRXwJSSliWRayZWbNk8taKjjmmvmT//V89xDbkPhpmSb++/Xhz3fpRK5cuH5Tr
                        D2JgZTmikSj8AQsFhbkIwjj1wlvPfwsCuGX/yXlXz5nZCYUdWn4EKD1B+abijdJNcyclAK8o1hnH
                        J4vtgmTQ9m0J/uCA5mLNbfnSaK4UYBLkFAJCKunfQJTfQjAlIehUlFVwU1MDKTgWEDdrN+/R8OTz
                        6JQunO4CIkMmg6Vm79G0AC3BEMY2FrceRQmzfCrZkeSrZps1Vl+PyzVjhnTTn2EGLAFz6lQ5PCfn
                        nVG5I9acI8hXr3SyKBbvPNAArRHwvSkRD0Gbq9yItV94TrDFMg2wZn31ueMKdapB3va3ltjzu5fk
                        AIhkBIPWGkQEIQSEEHBdt0tcZMnyaWzPcGWQEwQgAdHKzJuZKNGxIVr7dbf1y+WzOhtuH8MwoTWz
                        TB+/H/8KBaOKZo3qN7hlV4PUOO1QMQnbqqvdWNxQXzuorbURo0ePBmkJ6URR2jcPpTn0w8tuvPJd
                        aIn79ts9dPXBSyJXX35yxS13zmhQjtfBZNK5ENGXW779Cxosf4u5EQCYnnaknJ5xvkz3i0J45bcv
                        Pn/TCIm2ARaJHMBMKQiAVJCUUa8QCAOxFkAoQSWb4tGRTbk5jbmgiK+2aUkgEhm36S9/GVubGfQf
                        dtM/n7yEfV6ft9X6Sob3tlIKIKOHhSJjcTMMfOF4l+8TWSHxDaG7JeKMC9E3P/flHxG1HGoIM+mo
                        jv8Twn5v4IBh763fsGyg1BL1Cwf94r7njqgDAXyn91XWGrfcu6AdAGbPrsid5OWD2LoPIaC1l2mG
                        iLIiIstO4XSK7AwZQUEECKF98Pwu4waJBAAj0eY6ADD74p8WTPr9Pzu+7vZ/dfR2It2B4EhbFs+7
                        JDzY9r09cFRO0wik2iYKkoMZok0I9hEgHDcxNpFIoKy8GP6AhUhHO3bdZdTGJBrmXvGbm98FAAjC
                        OW8tjEKYdAvPaCAyKZO3o/eAr9MjtfE1ew9OzwzHOxiUL5o6ey/icClT5yBDuOWAPwVtbwHbcYZO
                        AhS3zbI2pcJCc8pvcFFi6QtDWt7cMmSz6GZt0EiX+mZIK31qXMmwza2ZtZgZSqkusWUYBqSUXZbZ
                        3ksa2VXfz05WSHzFsEqvybEBaMaMzbCmDIALpLhmmc+cPtqQmZv+Z5d+WDI6WPvTyqLwPiz1cCJ3
                        nELcF/T730s5EWzcsLhESPqrc/NFN94HOGRCsIImAa9cOImuqcmkI5sjSDsVdUeIrBdcls8Ogby0
                        6QQQkdCaNeA5xAlyCgAIIq5WWuUT2+/58vMsAOgMVAQAdHzd7f+qUEpBKYV0ThZ4ztKe13TGqVkp
                        4OKLV+5nGPMmBy3HxyrZB2AfwH00W8sEIQhIn1TJfV03jlg8jD12G4vOzk5UVRe1GpScc+NtN5+W
                        3uPWnbM3u2eWjB2Gl3/2UTDjRpGZsUspuwZbTvt0iW6WVC9Mc/tRKhrcZWUVNNkEZiq9NesNjtwN
                        oTEHLB8ueV0FU+MQITr7alAS2mojlbtcwYoLmIoQ6jSoqpWwpIDlhoBBOmWq8k3zZu2z6e0mb8lM
                        uuhy5TQAMEN2H/5tE+ieApwIXjmBbsez+/JuxhKR5fOTFRJfMWR4YmKQ5SWimTJAOl4nBEwfpeUu
                        E+8LLZp7TvTX0x87YdeC8M+YAxHo5O5SynGWFViVn5cTDYc7RjJUs+oUF1z3uytfxg3A1bueW8gf
                        39u+zQ53UjshS5bPA4PTCakAZm/+KwT8KRdJnxU7kLUcBRjNRFylXauuaIgxEEDrj/W/Or7utn9Z
                        EJnmgAEQ69a5DpGXT8MQgCHSIZYEYo300fGSxV1y4W8OMyy5F1BZrZQKCKHGECHIbG0GCKzy1yk4
                        +VLVT7JtGyvXrcbIkSNg+xkBJWAabuOgjo4b/xe/T2vdY4LRfXA9aIbPLt0DWo9O9Qgb12CYZJGr
                        PT8MSl8j3SNGNM+UcICzhyFYenjdrileWz58/+Z8F4kQQyfBRlKqnJUGSDF8EUIgYiApNcFhtsJO
                        pE/r3Q/uvpDTRgUSAO7JnJSM02TvMNwsXwdZIfEVozRgmMD6dKilUgZMc6tJ8qM550SnTb/jTIsD
                        A7XrmoTUFCJz+ZDBA5Zv2rSxvCXcGnXiyXNv/u1tT7Jmnallccuiu9tBettaF9l1vSxfIpllDceR
                        sO2t3YUQAIHzlVIwTcNhRjsJWSIMqj+q7MrA8011CQgf4VufqMZAj9wrDCjXG9Qys1c35d11JGCf
                        +6vHq/y++sEw9XgGlfkNG4pj5QqUYrYatDY6mQPL/f6Sza675vBkSiKVSqGqbzl8PvuZeLzzWMsU
                        qwpC6tGfn3vPZuDObdrz6c//O7TWXdFd3Xl9iheumbFI6HS0hYABvYNz60pG1QEvBPYevDY4Jv/M
                        EQU/3ZzvYNUAJjcghGsDZGhlbgZ8rSbirOGYAp2mRiKgkIoScmNvvFm1dtGiymj6uHZz9uVMHRIv
                        cydnLA5dFpNe67VGNsT9f0A2IdVXzLhxR4QWL34xikycNLZ2QL845vmSIWM3ns5McSHcE4h40KCB
                        w9rD4TDa2pqqmY2P1n3w1v89NPvJBtOyoJWCYZkCgGAtpReHtxMhkV3JyPKF8MZQ1/VM3USAlMC5
                        l88dV1m46CHSqhSALSAeI+iS9nVbrv79Y7duYZ3i74SQ6Nb6jLe/oJ6GviN2Q2jgiMacQJ9/jVJo
                        2o0MZ6hSsgigToFyQSIxPqVy3wHMKABBVLWAua3IMOpO72ir3a2zsxODB/Z5HYwO20r8wAYemH70
                        w7ehfqSF43uXYf9qijJ1P00ZPyoAEIbhFabSDCGoK9FV12fT/dpxv3ykeEBRS19IswxIFgPxXCIY
                        NnbPAYQScARxQZOGlIATZOg2gi/motFPFApHE8FNf/3rjzdq9o64Up6AMAWR67q81Y8h4zTZ2xKx
                        IyEhdyIksmsZXwZZIfEVkzm6XolZL8EUAFxxyUsTcnJrjwWSewpSExhiY15uXiQSaatkIJVK8R/n
                        /rPqydfnH9/e0ylSdz0SWYLZTb9gbn/H2aWNLF8AIhKaHU2woJQ3mCgF1Nz457+wip5hCXsjQA2F
                        eXlvhTvDvutqLrjI+6aD74KQ2LpsAUzsMy8wt2GfBJF3P0+7dN4om4QvgY9/oNA5HkIOIcBkUJvW
                        KgqmRgG/A4iEppLlbmrg6pPOn1j7/Gwg2vjH60wz+SMnER1qmXZtTq75TDweqSr0pWbnhNvmXHrV
                        Q414Z6zNJy6O9mjPDnwivgwys30AadFIcLqdvUxp7JFnI/jDomX5IaMxZDmNVaySNhCtACfKCZQD
                        QACUYNhtllvZKuB3AaUM6tvqoI2YW4sUwkkiQ9511xlz0j9s+8f/M5bb3jHb9+XI8uWSXdr4H2EI
                        QErw+Wd9PLCo9OP9cvPkAYpVf1OIicFA8Su2D2hta8nT2nkt2kmP/P7uVR8qeXlSpB0pNev0WuZW
                        h0nNTrea3BnrayYOLGuKyPLFUTqlBdmCmbV3/RneNQg5nEwT0Ko/kPPPjs5OOJ3lj3zd7f2y4bTw
                        JwBvb9knAQADTdg/O//JwzXV/tQVepRSTl8AlkFmitlYw8qsI+gUwUxoat+UiBa/OWboKY1TpgA/
                        Ox/20Oo/nUoisZ+UTn+fFVxEIvBuMhqB4PJ/XHfzBU8CAMSfgf7bq5z65Q6I3ZPSZRwsuyep8wkv
                        hTcYAAOTz0fJ4fmPjuVU8zgidxDY6iQWUbDfBCww7A5oIwEz1EHUt05jcQFTTljBiG3aVLFx3rzx
                        sZEjo9brr4c6jW4JqDzLBsNnCQKAlHRZCIJJXhuF8JYypExxJvrMNLMp/78pZIXEV4xIV6uzBOjC
                        6x7bpbiSJxMSBzLbJnFwU3FB0XOtbS05kVjEVK7/2b/d+sl99fr++J1/lJg8muwnF7PjecgLCENA
                        s4ZSDBIaY3/us5fNRK8bKSMuMko9G+KZ5b8n42jphcUZUMr7HxCdIGMzoAoI5BCLjpt++/MFnL7s
                        yABwPAwA3/qOnjVw5mGxgpKR/67SSPY//qLWXYjUQcp1RguyXdP0S2ZjHbHZAUW1JkLrmXwtgosb
                        pt3cb8H777+PUdXetoZW3nSmYHcCsd0O0DKGaIZmAVjtiQ0XvAKkI73QLRyhe1t6te3LNjhmRITj
                        OJgwYUII0xfHTpyK8nL/M2OhGof0s6gYknNIBwWABLQvyhwMAz4HEBrITQLShOuXoMFq6aYn5v7z
                        6d93gBQsI53PBjkJgXT102MPCLTMnpfULFmY6JFES7ECc+b609xd4Hh5Hlh+Zp+wz3ygvpqlo+86
                        2aWNrxgJwDoQ9qX7zh4ftOonQfEPiDiXdLAO2mwwyC1SycpZ1//2sOdYwfFytAIgFyRswZq162ZC
                        l5BOWsVby47D6mV66G6lALJCIssXQ3riVRqwLAOOAxx33Kyq3cY3PUzkHgxlvCaEGdZJ34PX33T6
                        S0A6qY/x3VjaOGIcQmP3f3mgg7WjyYjuDch9NcuRYNsxDKsVbK0DjBhUYLVATq1FZatjqw7+eP/9
                        4Rx9HBCtXIFXX30FRx8yFedeXFNlBXEDEcpZYxeC/ZaB/MXM/np0THryzhmlDZmQ7dl9bg9M2nJF
                        gncyjn1ZQqJ3COiRRx5ZPGjQoCpt7DsKMlJMIlEthM4Ds9JkRVjaHRBGEqpyExCKAbAAaQOlUaJQ
                        ynWrt9x9N1aTkV4SSY/4zBpaMyzDhGLVzedCQUw2TcyCkuyy6JZKNVOLqGdb6fP9/qyQ+ErJComd
                        0lW+MF0QOKOYvQtMptOoZi6/TL0iqwa2WwPHIpjTr330ckYsjyjxC0YqXFBQVNfe0QyleZVS7sqb
                        brz+PiE80x5A3vbS2826OGT5Imx7d+sdfHL7S2Eqc9mzBSLgwr1QXHbIzOmdkQ1TiwrLFwPtwoD1
                        sVzwwUXXzH64dfYdpYFJV9QldtwBf8aOOd3wV2lwsB1V5rJ0hsRroVmAINK+C44LWBawy5EILXkJ
                        sVTaWc8HmGPfhv+jfRGtmo1AwyQkfnbp8yXP/u6oFgCAVCDDQI0gs0azXCZqzNGyRgJbc79Mm/rC
                        8Fhg00WskwMMlv0AgODGAHIBJQmU0or+Y5B/SzQRXXzc4ZfV7XV0CADwfrQeuaE8fIIQXlq4RWx5
                        KIpdfDPPNVLyxyT0GKH9LeDgf0xD6Hx90IxfHztq6dUXgG5ZhKhSgEjX+DIyp6uryqcCK4WlyyeE
                        xo5d0rX0MW92iQEA+0za4qUl50xoKpkA1PhXBwUGtVeZj6edN6101VGtAJ8J87xLt4wkvbiKZEWK
                        ORIAmvoTJUr9oq2UtZEiGA5DxJjRaCGvBRBSwvEbFFqvuKMQRmueVmbUVf6Nd993xnrW6WWZbAf2
                        vSArJHbKpwsJBiAIhANg4Q24LnvV/E6+GrLzzy/ukudrPE2QrtQc2c8wfAtc6flOKXZXPfly9OqV
                        798SNgyC6ypYVs9OVinAzAriLF+AL0NI7D1rRP47x6wNCwFMPxNjAlV/+4+AcILBUGs8XhsydOhv
                        9OeH776i6e3EsuvJHF2TkszebbPVcpbh8wuJVfVjg5GGJfHE+HVyOrwwalJbqzgefRgK+o77+z5g
                        04FJJoQWVDBiC8cXjYKUbeyrrk06+W1tqerEs7+rbDn0qL8WvPrcGR09DocJggRf2QcB6xf37+WK
                        yGEascNd8uUTEQSzC6YUwU0xRAOUXCLgW2eCPqnGgcvPvWUPAEDU0xFYjigKEMJdayD2GwLMm/b4
                        BE417GmmUlNIiJTQ1lpmsymIEbPKGw+bf/65iGaEBABIDQzf66zg2vfvj082Yc4ClHbBZGaOp2k/
                        //xRefvttyReWLhOolv/RGTSAMDa4BkCFADDRSYPhHdeKq5E4EB7Y155/K0fAprgcgUJp5RVThwg
                        JoLNbLh+qHYDhksQCjBYQ4YtKog4aNHMjk/Ikg0qWZx4461Y2/srD4l3RZBmBcT3iqyQ2Bld0Q9d
                        N3CPMKMDZwAjE2PtsnMWxwBg42X1Vf93TWXbv3//2viAuflQoTpPAliNHjUyvmz5cigt16RS5gsL
                        FoRefuWVMxo1AwcecUvpxPHqWkPQmESKHvztbc5MzTVeh/l1//4s32p2LCR6CwrR69FDQUNDg5QJ
                        IYCbps26UZjt5wtQA0BxoFP6YpUXX3rHz96ZLCxzJnsV4piNL6WC4kCQvQFwdTpDoet4qY0N4Qnt
                        IRbsn53/yF5Jo2lfBcM1DCW08Lcgv99G7lwzgVRpLQd2W0qxd0eAZY5QnbZhcDmWTr0LACpHPT7K
                        5JywSxSIiHXHp+D+FIQQQII1kzJRDFADFNazFs2CRT0hsNJ07YUFzj4br75pN41c4E8znsHKlatw
                        y91XAQCWYwUA4LVVI/DRrIZQZds/jieETwyZuYjHO/ta5FsPgH465ooTPvzYtVqNWYM1bdkryakw
                        oPNgIw4YMdvxt2m4IVCsUCC45Hd/vOJlADjiiHGhF19cGK1Z5sVFXjsiJU3TR1IyG8bWE++gm8Mk
                        gIuvvP/HUO1joT3nRSIuA9gUWvg0W2HW+Wth+hPQ2gQMZasmCZBL8EmAkgYFO1RkfO3TM4Y1rUnB
                        MX2eZajrhKUjWrrXtsjy3ScrJHZGRj90mRaph5C44O6J/qKpcyMA4/oa741p9NgFQhtjgOi+zJGN
                        xcUlaG9rrVbSfqm1qfqxMjFpzZnTgaoq4MIL79knPz/+F0D211o8mXLVy+Eolv75T79eIOhAk3nO
                        t95ZLcvXxxcXEh7EwFkXvTOmf/6qPwDaLygxBKCIQOylgmd/MO2cxT+IsvKWC4hMYmb+MqonEohc
                        SDbSlow9Br8aHD2qPmAnTlEFI2fsBqT6a45WOaZQCiIMMy/JOlAnfKlqlq27IRHaAGKDEKsmFC4x
                        RetAouilfjP4DwP+pUB8DEjvSkT5jpOqcDVDCGOTgN0ObS1IWlHFjDhctBH513dsyZn7yIPndwLA
                        a0/X4+DjKoEIgAqvvVEAT7/2DPL3ysOo0Cj87vxN/W3zgyNtiv1KKZlvmr7VWqvcWGd4SF6o+D8W
                        F//LRec4bUT2YlaDpRbvAtTGBpIAW5YyFYNzDMMdxIw2In741tuv/6vZq9RH17Hmnq8xAZdcuXgv
                        1ouGQieKSKT6QLtBwEyCKMVkbwakBbgA5TSTWdkAAKw7gkSGDCTjKcFFHR0bJjT++cW+bT1Eg3eC
                        0vtSME2TBgyAtWZNyjEMG1m+P2SjNv5rBpkAcN3UH7gPtnziv7IUyekM6CvmHWIF9K7RRHhKTrD8
                        JTLjaG1tDDKbHz/+5Ik3JdYVupUVwJlnAldc+viPfQF1nCCzVDPqtBbNZJgNAX+q2HUVdFZEZPma
                        0Wkl4TMRvPaapRcySIJUsYZsNchanYjpZ65euG8UcLqqKA4YAAsM57N51H96HgANZlcyLGuGLfUU
                        Z7fxidzc8vo9gNtGSqQGgXMbBZBvqtylBpwcxzSTSuRHydncBwi2E9TezFanQKgNEIXQoiKRkibs
                        5AlKxaCcFHJycpIFefntqYRsJrAQ8K2DNhqIfRuFr73FJXNRbunAT/becy988sH7eO39FTj44BE4
                        +NRKAMDDT7+KU089BADw6gev4riDj0U9gDMuOF3sQj+eYAn9Y9dxq4QQceUm+rrSLdVaAiw2pBDJ
                        ZbhSa46A2GbCQECEoM21ts9WSkkDSO0hNSKazS0MHnzJZX//4V13/d87ZWUINGxBwjJBUnsrPUoB
                        lgX7iCMSuX37vlsUMq29bWquhFADoH0prfybADZAjgU2wgBvAUwpKRQDcqPs1EsibcYcbPrLfWdt
                        7tKBBNyHbZ0yAZV+TWdqf6T9NhzULPOZNaOz4ZnfB7JC4nOT8ZEYBgB4sOVBxEtakoMGtQfcax49
                        1hco+CEjeqTfX/ya0tJmjjlJF49Pn3b5C++++77/kZl7ufPnA394eN74YH78/8jAJMCUWlozoUVY
                        iI4JpkGrLcvA3oecWvz+qw+3ft2/OMv3FyM9Xvz6qhnnCcPdHdAmkR7EWs6T0n72ht9e9c71t1zW
                        YwluwwbP3CG+BMu2N9MmaD3FEQL2xeev249F8kRoHslsLhbk5oGNJu0WLdKaVJ17zMbn/4zkqTUd
                        DeuHFTSOfeepS4DwCEFGBeD2NQQNi8ccJKNJ2LaNjrY2tLWF/dyXy23D/w4rYxkrcgFSBgKNa5N4
                        4bATTkhO2XMEAODog0d0te2DD+qx556V6Nu3L1at8nyfKgs8QfH7abcP3ju33yWm2/DDRCJeZQtT
                        M3Mw4aSCHR0dqOrT91Gh7bc1lGZooVnkCuh8wGwFoAARdFKu34DuS/CvYFYpAQQJhhYI2IceioKW
                        FoQNAWSS3B1z9Iclg6oX7z31/LbBgqhECC5WqrDIEP42aJFkWGGAE4AWzLZjUd81Ck1MIlrCqsEP
                        OB2//8OvlnS3IhGRnUqlnEy1TMDLfMnM6bTaQPf3ulPTqz5Hlu8uWSGxM7qWNDIvZJws18YB4NJi
                        AAxEj3lyV9PEIRqdB9mWfwW7sXxmo66lwak5/ciaVRee8kzpq7cd2/zCA8BbesGQ3LLNx0Rj7cfm
                        5eU6YHMxA1EBSjbU0rN5hdzfdRxkRUSWL5/Pn6js6BNQtcfIxM8AhADp0ywWMDlLm5v1W8xAzQzT
                        rpmScmrgo2nS5Ux8/xfLieZZKizTsLWGQxq47KJbzwM5k7XWQxVzsUFiCXH5cz5ZtP6mPx+5GACa
                        W+EA8D9cU9AIAGe+tferprkozNw6CujcW2l3XDyehAFGff0WBP0mqqurYZl2LVi0MJRLEAmT8t/P
                        2/LLt56YuUPvVOy5ZyWeeziKo4/zxEVDPXDrrXeXh0Kt++fY+Lmr3ImRaKsoKSnRiXgEgYBft7TV
                        ico+ZSsAfKyV0+KyGbEMI6XJ2kycrIPhC0LLIhjBjayCmxTc4YCTD8RHASIK8hPQsufIcfeXvrbv
                        Wf849EwUjCr69yiWmwb3GxwZ6bruIIASWvg2Ktirfcg3oeyogBkHCtsKCyzZ3tFiSNTmulgzQCG0
                        MpUYvPCee3+11jC83A1DJu8VXIf5jp4pJXOqKzJEpY0NQohtQjIBgMikW+8fFACAxJnrEjXdKm9m
                        +W6TFRI7g3qlpO11a1x83rzhhYUr988Jxk4AC1leXj1vS2NtNaBjqUTh49ecVbNq/nzgr78+tvms
                        s4CkgcCgQ+dfGEuETw8EAgAby7X0z9MOrQDJ0AMzrl5FombDb/9gQ0rZoxJfliz/ay79Ecr32Gvm
                        VABglsMY+mUBe2400TavtDJn87Jun/XdPTEX56ATAEj0csL7L9ESDjRw3dWzDnJV9M5wRwTVfasb
                        Y7Gow8odaMFwjVSfVjwF4Oew9zoEYu9dWosHnlHc3vr07D1tyzdE85a9gNh4IvgB6jRNs6C4IB/5
                        +fmA9gZHKd1AEOWvXH30aU9FIkDFKCDaAdSnwzhz4YVjRKNAJALkAgjlAkf/KITJP50rqnedfzhz
                        bEReyCgVhjHRdXRp376DOtctX1fAjkRhYSE2b94sykqLP9FsPcYquV6wE3GVjrtabXrnvZ8sf+ut
                        fTk/Hzjx1E0lE/brpy75JfDLX3YWBIN/nwr41hQVlXW0tTcUA04ptOU7v+2ua5Hrhjgl8w3DVJpF
                        K4mchYxgIxDsAJK5GuwjKJfhb2bEZGtHc4jhjypUL0ol/Yn77z9gvetuPd4GGVgz86M4oCDIpOkM
                        YBmM6aNc2d3q4PlE+Ezucq7l9NKGEe/+mWxZ7u8H2VFqJ/xr5dkD9h2+si0Hczo1GKxMVFQg0NSA
                        xPSrV+xXWhKeyIQpAv54QV7Z8mSiHcRyczzV9PLtd0x9EfVA5REAGoDx44H6kr9dK4zk8UopWGZg
                        MStrQWZfte+d+iwAsK5xvBSwOz89juPAtreKDu/mNbIi5HtG90JLPbzlicDshUpmKkVLJSGE8LKl
                        EpmKpWQmCBLgdBEm0wT2PuTXxT/ZddI+wowfFk+Gd/X7QvMUyzdcl5cJEWusqclBzTKgZkrKAQxc
                        PXVOZ1d+FderT9G9xhOzlxXTS6i2tdpk9zTNWz/b05zhyECkqKhIbqlvNGOxaOHChQvt4cOHj4/z
                        omeDOWvev2zu8t+ZL417b68fxt2QWNu/+dn8KoNiQS0a+kM7HcwpxSw2lxaWrSU2hUGpvvF451An
                        GUNp6cBGIt1kO+am0CggBCAa8XwoUZ+HimEhIOpZHHKHAbkh4P3XgOpqYNpFUVG56xu/V+CJZBjh
                        YYNGf7h69SKf387p3Lyhtl9efgCrVi8TBYUhNDU1ITR8QINBSd3htG584IF7V2A7gVmPP9yvBQBu
                        uxOFweCs/wNiewD2kra2VoDiPwI4F1qsA8x10BwhYdcpztkEsuMQrECGYqQEwC7UphzA3wYKNSU3
                        Tdxw7/OHtrIEdzdM2Ra2Gx7GmSyTo7fNTmoYdo+qqNsL0DCyKuJ7Q7Ygw07Yd/jKtrdXDi8CAAGC
                        ZcH+8A6om6Z/eJIdWPtjqetu9Pvy4rZtoyPScHRHZ2N+Z1jf+/rrN//jgQfOwllnAUdOAurrgc1F
                        95wnzNREEHIK8ksWs7bWgNiGCq1u3zjh3b+9jmYgk4L4s2Hbnnd05qbNPGZFxPcDpRSUUiAiTxyk
                        zc7eDJEhaLLdXUQAgGmYW1NfM0uCASUJUnrjiellIxS7Di7rHwyuPRcyuavfn7NUUPA9Jd310Aln
                        xYpZm4466h4ruLbEqoGPmA0otXXgMIyeSxvM3mBjGIBpUtd1qpQBL2eSASLTJBpip/8HkYmj+yCg
                        FeCPHNG8csVac9myZXjjjTfsaDSKdevWIRAIgAQGuVh3fDLvpUsKrUU/8tmmnLDHHoCWJqhzKAzH
                        1jDfVhCftLZvgYZsEQgukFKivLwclmk3MSQ04m40Ajz39AqEKj2rxLBhniXiuafrUVEJ1K8CIg3A
                        XqOA+/509+F9h981V7Pen2Bil7F7rd+wrqFEUKAgmdD9Nm1sQrizFT6/gUi0A6OGjLn5onG3TGqc
                        U/6nO+64YwEALN5Ut12rzTN3orBp/T2/AFpPJJIjgdhPgPgPACMM+N4Fcp8Dil4D5X/AVLqIzD7r
                        yChshPDHQGY6L5423aj/xc2L+NXbfj/lo3ueG9zCDIYBKA1Ixd4J/1RfFmP7f+naGzv9y/K9IDva
                        7ITVAMqHr2xT7Jn9tIRz6fm1QwtL24eDWqcM7j9iVW1trd/lzmFE9kfRcOqOO6+6cj4qw94GxnsP
                        19z30iB/lXOGILsv2FjDWnxMmloBRFW4fP7dTwxfD3he8kTpTjiTYu9TcF0XzAzLsrqsEF5xJbHd
                        mV6W7xaGYWw3CzURgYjgyqecjIhQyhMXhiHA0J71Sljp68e7TgRBpFLQU895as+iUtzNSMI0jVUu
                        u2vh5Lyp0KFnPT1r3pq16QyKmSU/AoThXbKaAZDa6YyUOS04iMhNMbP2ZrjSASwfSEvw8w1IXHMe
                        BlsFb1UNrxyO+rqNcF0X5eVlMAwDpkkaQIcAaWbZ15TtDql43sfv/aOTuHMMDFEHliVMmmAWzTHk
                        r2771Zn5eO7vL49VZeF9BblVUifziOR/EqsSi0PD6nH0sDzMeuh0cfzxR2jgWABAZV8vQqPSe8Cv
                        a/66n2G13h8MlmxMJpOxAX0HY+nSJcOXLlk63DRNlJWWY8jgofHqvr6W+vr6fFv5Lrrh7mtmAcDD
                        p5/PAHIAYFy/qh7H5MwzHxjl89X/0jZwSCTROZjZRk5OTpxJ5TLb74Pz5hiw4gy7EVCmkMoAnISk
                        SBKwXHCoqaNjcNtjj41uBeAyZ+7/C9L/pLgGPoIBeD4MO6qOuZMiH1mydCMrJHbCK/jEdVftHRw9
                        yIBhembfvLJXzwBwViCQE62t3ZQDwIQOPdhZd9gdB/ygctORk17ACx+lN1APXDZjYWn+6A13SqmG
                        2SbCUL63WSTKGVaD01o+e9H9P1iOewBkTNAZdwxm7EwHdC8xnqkKqpSCECIT2/11H8IsXzFEBCll
                        D2tERkAaBqWXKzSICKbpXSMEAWGKdNIoglae1eLgPSOF109/9OyiUhwBWNpn+WqldOBq8Z6msP/W
                        W26ddcsttwDQEAeatnzdyzTZ3bSZWdLoWVkynYkyjVbe50wLYN1TCVk+2KzhsAZqzsEAf59ZR6Xk
                        xt8t+nARRo4cCdd1YVkmcoI5rcLQDpg6BbSPBKqVZlspN56TX70ewj8vFmkdmFM2/u9ObJe28af0
                        bf55ALj6jk/6G3L54Qxd4bP96/yW3ZpSyT3MgU41gLUfvPYKjj/tRN2w6hM8/Z9nkJubh1OPOwTX
                        XP0crr7laNTXA6yaDmBhfBSLRSuUcqvWrFnpJxLRsWN3qW1vb+1bkFfU7PcFE+FwKwDEz9o8bRbC
                        APK93xiuBfL7ev8fcsgffeNGRiaT4RyTn4MDAJiQtKwgmLfSIRRpzSsAagT8rw0dNHLj2nXrTIW4
                        QRTaomHEo9E+TQ899JMGrbcuQTz0kBcG+sCrg8w+gc6u47tgtU9hD+hsREWWL5NsQqqdQDUwUzWQ
                        mY7yhuvuv4EoftaIEaOxdOmSUp9p1WllvxzrzHns9lNP/jCTmAYAKqsIpx/Bhf5xD17tou08y7Qi
                        AC2zjJz3U6kEVCJnzvV3nPZ8Vyld7WWE80wSGqw1SHy6EOge183MXSJCiOyq1feB7vdvb+uTUgrC
                        8DJMEm1dx1aKobW3xEAApPSWMy65cOY+eYUtNxDpAWD/OgAyTyBJbG1wuKWTOv3PNJkftfz2t3+t
                        JTJNzSxdBqquQmDLzUgIgS4zOaVLLShldPOFAIQASRecMVaQ2GCzHuCQgDl+ULv94crCOBFwxG4I
                        7Xn0fVeSUJe1tDT7tXKxfv16jBwxrBWEfAviCQi1p9RqJYAEC7FWa2rz55W+3+CMWnb/LQe3A8AJ
                        8xA6Zx9w2pCA+6fWlhL+fqkBdXBRYRHC4YaRtu3fCJ16iJcVPHDqb/o5w/bcG7NmnSGOP/7nGjg0
                        /c1KPPzwwzju1FNx0Tk3V4SC7l/A1An2kWLfx7bq+wmMjWdDUWV+fmEqEu4MGqbprl7zdr8JxZOG
                        XT3jF/Xdz80ZU2ZX2r7mUtOu+xWRrjYYw4kQEF6B3y0FobxkZyQilKHbWdNa4px3CVabRpIBkkyB
                        lt8XXTCfrochXUjD8IQZRG9/hZ55OjJhmzuKvMiS5b8hKyR2QiZrxIkXoHxo8aOHm7xlamlpKVat
                        WrFrQUHxK6z08sba0L0Ny86rfWE2gAag5iyg5n7vezc/+u5EFCx+hqEFWGzQ2lnOWjQmI/qJzrrg
                        pj/945cbe+9TKwWQhjAMfBY3FmaGEMJkZplxvgS2OmJm+e7S26nWdV2YptklKnrf3UoxLFOYUrLM
                        aM1jjvlP9YihHT+w7PDehjCqQapUkJWfE8xrdOObxhEbC0RH9MrLD7l6HSbNw1FHHY2bn292xwwk
                        4a5jxxoE+/hq+GfNRVxqSEGekKiBj65VkrsLCd0722Uvf6Drrn7yR8LfeIkgMZhZDlm0aAksy0J+
                        Xgj5+fmOgPksCeT0ySmZVRwZ8ub7895vzB/LwZo/X91m20JoZg14VXdPeHp96JnjBsbPfWx12Xkn
                        DY08NP214anWedOI1LAcXx6SqfhoA6yDduHfpRN/76aDr3wKR9cD0U4g9D5Wr15KQ4dezADw2mvL
                        cfDBh+Cnk+8Ww6pbJynOMzWVf+IkRnZcfu3udX+6Jlpu5v3l9yasYhAXVFf2xeo1KwcHArHW2/5w
                        w6ibj25DwmoykiWLhitz3ekMMRhgiyiwhDk5zCDdpyA/D24ygWQyUWhANRFEJG6kngMoBp3zMQCQ
                        azTcee8Vq0ksM4HRSmtwb+Hg9QfeWhX/zzr4HSUWywqV7wNZIbETpAQsC+aFl9aPKch9bmEo3/g4
                        EgkPNM3oUgIFTEO+duhJD9WMr3wBDzzwAM4c7ymIa65BfmC3J6YIO3Gx4yZLy8pLV4Q7OqB0pJzZ
                        WLBxWfMv//qPGzd2eaHR1h6VtWeG9qYWny4kMlEaRGS6riunTZt2TlNT05ubNm1qffXVVxu/7uOX
                        5asls3zgOA4uv/zyg30+38BUKrX28ccf/6i5uTniKgnT8ISGZ/Ha+l1BNeZVV+VdalnqUINySwm6
                        isjKZzZXEYx8x01Uhczoyz4VfC715N8eu/L9j/iWJ/azrp46N74MjAOO2tdqWfy2uvKUfxxqieQw
                        zXZzLNqw9K7fXbiUmcTs1w4OHnnQKx1dooa3+vUpBRyw++OhPXYZkRsoXlvuy2+eAiQnhTvDAw3D
                        QG5OQV0iGa9atuwTVFVVgSCRm5u/zoZ4a/Lqq6eOuRd44pr/mL94+LAwNHNX1WkAP7vx/0JPTvt7
                        FADs9A107kW/PcOk6GR2rShITkhFk1UlxeULBdDpd/r/eWLgmNf2+yX0w08/g1OnHos337yc9j/8
                        sK7O8e6HHhdTTztbR7EnJp8+WfzhwZneDRsF3n0f+Pj5hw9TvO7vAbtkPgCQRunSpYv6jRzd92nW
                        aJQs8gXpoZpDUcCI5ARKEE/EDYJb4PcHIJMJk+FWsor2MwxD+mEuAKgzYbQ+wTH/63c8ULMBAEgQ
                        sWRWSsKwzK7w9Nm39wnUtVcY+f2A/lUNap9JLUkAGEjsFe/KFP3qPa5vs3Sqej3uTAj0FhC+XlvM
                        DjDfB7JCYicQgaZdir0p52/XADpPoWGiaVrLTSuxFuBwUeV/LjrvlLLEAw9U4swzzwReGI8Hrgca
                        D/znESnxyVOppEZeXh4cNwYAsH20qvOT8T++46n91pM40GSdSYPdzfveMIBM+Ntn8JU899xz9y4t
                        Lb2fmdcTUdWiRYuOff7551uZOb7zb2f5tsPMuOCCC/YpLCy8moj21lq/2dnZ+ac77rhjjjANGGmn
                        m0yFWWbgskvvPjIUSp0nyCwm0rsTfFIpZYc74tBaIy+vCLaVPydXdTx8wNxznt3tecjbJkzAlWvn
                        OwAweML99qRDIiNCgaL9iTuPI4FxYGOZVomZG+qLnnz0r2e0zm8fZI4v9BK3KeW100ynyvzVobGC
                        stEv9/Pltx8Oip63ceOmqsrKSvj9QbS0tAAQqKurQygURFFhcbNpcBvB/6+fbLrs2v0OhPO3xY/k
                        //JPpzSBpeeQYRKO+sUhgeeeeDUhWQJk4tjXFuWMW/LBPrHmugM5gcOkm9pNphh+vz8ZtEJhQfYq
                        K1X0ZM3BJz2FUd6xbOiIomJUCNHIBwCAUEVn+ijnoSFaC4R+hFyEUA9gcwNQnQssXw68+fjNLxLp
                        Ur8Z6kgko7t9smyFsCwLw0dW1RGoXSl/YsTI0fW1da3w2TZaWlrHEpE02UhqOCMikbAdDAZhG8ox
                        yV5VwEWPDqS93n70iUcWPt90W4IEkXaZyUiLibRfCQkiLVPdBEJ6IF8GY8OHEANOZqfHxZLuVlin
                        y5Rvs3KaFRJZPj/feSGxEPvlAcCueMtLlJO57jM/23CAg3w25mS+IR0AmDjuqdDcRSdGjzoUBbv8
                        YNnplvHBfszJPYFGRzMvDfgY+YmFZ519zP3tZ511Fo6Y/QKuvx6ouB+ovn3NuDJzzjyttXA2bkIw
                        GERrRyvKyspgJunnV9565Uyv/ji23ti92p2xTxjK8aaRbHg5AQBozYBBUArwmbCvu/7ONw2NcWDE
                        SPvnqFTolYDMabzy1uNewGdd2djRZfA/C/rQO3z9wNMs/6sPOUkTtgAgXDjSIlswK73VeyVzwLyl
                        IW0qEAiC02b+dA6D7r4CAMDkwvMUsMQDL8A/7Eg4BwBacyqd5tnuaoe3trz1gC2bbNuzmqHxBvT1
                        AJROaaatB1xphg1CjTHarlHLHLD3K4UASNT4NdcktznM3LOoFhODQGCYGHLohPxVr3wU7n6+zCoE
                        L53y6I/z7KanmpqazMqSPi+AkTIlLS36cOyff/XaIc0Zc7+gGqF1jb766j9d6LP5SCI9yiBUtLS0
                        IMdnQymFznAL+vcbsNgCF+7WNPTgi+johjvvhUa7t79//vrdqs7okOiAQc9fppE8iODsSkSypDA3
                        sbl2c25OjiXNFCYfm/ef14ak5kbUtcymCZOl5wh4wYHIyxv9YrmveNNhbMTPa2/rGOG6Lnz+ENrb
                        21FYWoacnBz4A0HU1tYi32qD32etN4G3++YF/3Hq1e6/8fZLwA8WpTL5uy/+8R+Cv3/pojgAHDoM
                        9n9WwbnkcASLxr89MRFd/zMlwj+SKtKHIML5BbbujEZh2cp148Zvpk/95TOhdChGw/IVyC3IQyg3
                        z0sWASAaeRihiioAo/Dcc0+jNncqfnQw0AGgvgFABHj9b0/uYyRWPmHbgYaW+s2DN27ciPpNG3HM
                        Mcc4zH1eLy0pTWmdgislOiINIzo624bn5uY4BFFPZqofM4tEshMBf6Dd76MHAwnnoeJ3nl+/z/x1
                        zmjO1qrI8s0n69J/kM/Gn6A/ePT44j0HrohNnmjqmdqVcxeeGD1iF4QmHP3BFaBEKSNxKEDtIGom
                        YKDtNp129jFoP+rH643n//WCKj0IxoN/h/rbnSguUXNvZstYR4bhKykqMePJeEVln8r1IuGryf+t
                        70Xcis+ewUMIPLCHFTzzQ46TID9rThqC8OOz5la8dP/EhmnT735USbW3dDRsK+8ZaBE27MQeFK18
                        6Os+tF8Wcx5ykwoMCUenx2IwK2+kJXiv2BBeDkRAGAY4PRD3MKv3EhFaA8IgMDzL+LAj0TV7IyIQ
                        qGvpyNsGg8gWAMA6pUfPdJzR6c/XgEBkCYyCmVoiHUMYMARhBr3hr5HLkgAw2Zxlz9THO1oD3UXE
                        p0Eg/Lrml8Nuqnl41ZpXPgpr9PQ1OOPoBUPi4VVP+3JDKC+rdEiZiaAvtDmJzjGRoZH++hVuhOfk
                        KH75y+pRl188qyInj48yyJjQGYnm2pYF12W0x9sRCAQgpQQYYR/Z/zpo2SGN974A/Pp0mHELcre8
                        9rLK0pWn55Yu3Cvlpg4sLir/OBJpWrNu3dohDT6RW1VZ1W4o58bJt8qXgLmAlBm7mppswhxYAqvP
                        ac/shWDjGRqpyY1bmuG6LgoLCyEMHwYOGFznwv6YSA7dvHnzMKUUkioJv89ymWGcqpe9/tGfy/In
                        nL2kBSA8cNrUgnjDbc5vTrhIn/9D+K1CiCEHwl8z/ZmJxqi6Q9vjyWEGrB9Aibb8vMItnZ1hX0c4
                        DNsOLpBO9LE7bqn5NwAgEsWq5csxbK89u457w3LvMYJcDKsYBaASRx89FavS7999SXNhoT1vBDsb
                        jiCO/t/mzZuDxcXFg+vq6hCPxzF8+HCU5Ja8rIxi2LaN2vpNlU2t9XvU1W+C3+9HfsFAGxpbhgwe
                        umntmnW+nJycYqHFi/72jnuuvv+e9cAdX/dtlyXLZ+Y7LyQKMTcJbGdinX5h2f7Qo0cDjl4Rw9gl
                        zuOSJRMgTNC0mgfPYVUwRlDsYDDXA4gr1pBSPn3xTWcsPXLCBLyw6AV11Cl/zX3w72dE5s8HRnc+
                        dh4ZqVFIadO2ffG4G6+AAbhSPjbt1ssewa0AFAMmeb4Q6a6Wuq3xAt11BuPMj9w4ADBzctzEWwsX
                        zb2q/aU/T2y4bvrd91qGOgLCBEhslhLtAqpDOtYGFdowDAbe+8wH6mtPN5H5xT0tE46jYVkWTCGE
                        w64GvIG/hxITABxsNyEzZyLiMtEvnPZ7sUk4Ka2FIaCVgjBS+gCQ9pLtMCht0hWGBkODkMnZoDRr
                        3ZVticgwtVSSDAFm1gxPjCh4lqPTcIAzhQAyavwAHOD4Ll8BQdTlHLjNeWCvvcwKN9c8tMpxFXz2
                        EL+r1yctASE19LTrHri4qk/iVqBEG0psBrNtWL7N4UQUJvTGi/909AcM4NBDZ5dfdln9/oFA4iRB
                        sQPa29tyC/KLEAgEUFdbC2aGdBwoNlBdPRCAjSTUsbX5odsBpPoXNOcMzp19g9ew+CRmVVpZWYn5
                        8+fv2trchFGjRiHkD8wBAyYFc/MHnW8WrqtJwASpFPiyiz4YnxtcNQFmx2n1jZv3NGIG8vPzPQtI
                        ZxTV1f2lAkFqWscQYcBc77ruMNd1kecvgEwGIwSjFrc/HZ/wM8Tx+n34j7M2dOYtd3eJseIV/xgd
                        ia/dzU9mXiSeGsaM4QA0CbVeqtTozijXgax/GrDfctvyPxg7dEgjADQs95YyKqv3xKr361GZW4lQ
                        AVBR7W23IvdYvPbwB/jgk1oAQJ1cOY7UpiOLhTkeEgJkQ2tTVlcP6GDWecmUEkoLDBw0DG2dsVyJ
                        eo0OVR2OtQxv72jFgAH9UFhQtM4XNGuHDRmKJUsWWARsspX/j9f/dvrjMIDby/YN5I7oNM55a2H0
                        674rs2T5LHznhUR/pJxPe390TUrOvr0qMOmKj6LAVo/yX0979mTSqcNNRASxjEh2G4OBUF0krnHT
                        TTfdevKPz8rPLThD189H5PDBZ0QqXwDe63hpoi+/89xENJ7bp6Ksrr29XUTCYZQVl7+Q2EiPAfBE
                        hMjMkvkzjN8WWGmQ8Gaii966qt11gOk3/O1q28SBYMOvlEJ5Sd+Xm5oac7VyGmTKXH/Db3815+sX
                        B18c2/KWClKef/pWiwIDJEjcj9n+M+WR8UyGvq2OqkgLjt6pl9MrRTozgDOEENAqnbKZBZTUWyML
                        0tuhLm8+ASLh5fzQQCaJUqbcNm3NzwQhCI4DffmVf5583TXBOzRj1TXXXL9lxQLniqdfvqmBdbdq
                        FF3N9AQKd4VRGl6khSWg9Ppk+uP2tGmPXGiI2K2s/WsM04qzkgBTJOU61dCBD379g9MfeLAPchce
                        9/TwvfZonmjZzilSyl0dV8G2AojHU2hubka0Mwafz4dYJI7+/QbBZ1uS2AgEVeFdK/u05/7rdwtG
                        DSpYeypU6lhArLaMnPUd4ebS9979ECUlJagsrwQRIRaPHxgK5SbJDcwsXIcEADw95d2qJTetnJqT
                        F/2/LVtqqyzLQkFBASKRCCKRCPr16x8ePSoPHeGwxSzqW7bsesEBx/yw7uN3Hrq+s7MTiUQCpRWV
                        kEqMzaPyR90zUWw9gLZ/T1VVK0IfDP/k4Y/DEbOzIpXqKE3I6FhhqaFSigQrgIQuJuKxGkobJjWy
                        Nl41EJpdxP3nXvnHowF4IiIS6UQFQgjlAh21legAsOePAGwGPvgAmLngd+VmKFXtsNpDWGo/k4wq
                        CNGhdWC1EP6m6n4D1OaNy3QqntjPTRetCAQC8Pl8WpG7bywZtX0+H5gliDTyC3KSoaBvXTjamffx
                        xx9bJsRLFgeXVf1z99n4HdLZvICsiMjybeI77yOxjdNQV8e91anotgdGBi89c1W8Yvb+gbpJbyfO
                        v3n1wApacAN0bA/TiZczxHrNiUYAuuY3l/5s/nygYjzw4gtA/YvAmWcCd/1l4aic8g/uM0iOsiwL
                        be2NwWQyiYLCwCMtm+RNf5pRswoApOvCtCwvSiNjb+/BtmseUkpYliUcl7UwgKmXPbN3aW7jXURy
                        QkdHhygtHfhAwomBmSIqFV7b3m69f9+9Fy3oHte/Y3aWj/vrzUfhOoBlAY72RB4D+PE1r5WPan9v
                        YDBEwwoMq9hos968/J6LFnhmA++4qq4omEySDrNHxAI0IIwaodR1WggBLVXayRVQMi0oGVCmC3OG
                        7XdPcZKGMMBa9DgqnPa76B41IIwa/2lnYVBeSFSHQuXHOsm2M5VyEAzmxIWKh6VDV910+zWP9Dz+
                        6WRivY535pmgN/xaH5C8/JpHfmaibpbPzm0GyDEMX0wpBcFaEGBA5t2Tm5uLaLRhGMA2DOcnIC5s
                        am63vVosGu3t7ZAukJOT44UaAxgyqL8GG6ssFs1B1f+vpfmWjoTD2Gw0jwLFDwPIDyBfkFEMQAs2
                        FBGhvq42N5FIoDA/F5UlQx/46SHHvvOvf/0LjrHxGAYXpATtwYxUXk7QicfjiCcj5X5foFGANoL0
                        OM1IuY7/9zfeeu5N0wH9+k9QfOCe93y4ZNmS/lJK5HIQ/aoH1PbP++HV5Ym9P1qkHzzOQdskDScM
                        IKCEkwCglYZFQFAI2CS4RIMtZoQ1EgtB1vNcbr564+W/AQDc+KebMHLkSBx38LGIpIfr3MwBjwBX
                        Tn+u2udr+TFTZJwpOkeB4NdsNgj4F6RQsBlQPmHaBQBQEMpFONxSSKrt/z755JPQltpN2H///ZET
                        9EeTyWSopE9xJBKNIJVK5cZiMeSGQo1a0xIitFi64Klb/njRc92vyaP67Bt4vuntBACQIJOzPhJZ
                        vgV85y0S23gd9xpYa2b47Joz4Yy7+oHQgbe8nay6EoFz8t49GVqMJdAgA8l2sGhOIaWbG3AV6oH5
                        LwAPnAXMng3MbwDuemLWTwvL22+QyhkRjXZi06ZNKM4PYWC/we9d8psPz3JTD3aZYA3DgJIShiWw
                        07SVAIhIsGat0zPoY05MVI8e0n4CIIJ1dVtEnz6DntFsQkBVODq4XFPSfeCBD5fef/9FICI/M3+m
                        tfhvDL10LWGrP8CwQ5F/2F7PjNzL1/RTX6lvCkNXM/QCXejKeRVXrt5ny22R9EHD1nLvaafLbuPz
                        Mb+4qXr0ADpx+nX20WedddZZf/nLXxYLIdLWDLFNW+QUJymQqWVSY0q3RqJ3UA0DB5rw73LGI8N+
                        fXnR3sKX2EcIjCELYxYvWY9dxozqdN1UyBZYKAyqhgZqDMOs0a7suSGPjMwVDAhRH1y//gA9bdpd
                        v01G2i7NyclBSUk5Wlpa/LZtxxKJhGXbtnKdVB6L6J6ReGd5NN6yPzMjJxRCY2MjNmysQ9++feHz
                        +dNJrAhFRUXIy81LAmynUnHhs0KthQXFbZHOhmPrYyloU1YDrlNcXOy0trYFAba1Nt8l0lUMoVOp
                        +IhgMIiCggJAS7iI/nD2KzOGatM5kLWCIczGwsLCiGEYkE4Sts8Hhmpk6HzWIqZihSfPe3b/V15d
                        Nygyfbr3e+dej8gPd4n3X7FiKQKBAKpy+6KiTx+srX/90Q1i7iZNThkYMWGiBQwXmgpACCmVGmCa
                        piSiFQAkM61y3dx7b/7jb94CgBnPPYwIoshFCBeef2FXNc/cEHD0UTebo/oX78ocHmAQTzR8yQM0
                        mTmGsJezppWG8K8L+Eq3mKYASRNJx0mCWWmdLO/oSCWB6C5gtl3XxYABA1BSWPFWLNG6e3F+8crm
                        1i3DBYkICSRDoYBLBn9IynjJj+DiG/9w0Ts9TroAnql7I6FcCSG+A+bELN8bvvtCYpsoJu8FTj9e
                        PwV6OYAlt5yTXALg0rJluyGVOoAQGCElTIuS+QDGyIRz1b3X/2YNVdWgjmtQcSYwH8D4CmBhXfOD
                        jU1NuZ2dnWhuqIVpmsirKEHfzqIbp/NDjkWeK7/W2oFgMGto1l2Fkzy2H76hmbXOrKkLmBdc8sEQ
                        05ATazdvGtO378ClZeUD1jds2Vys2HjXELHht9xy7bm33QKvj2XnU0SE3snzXu36qtjGtN8T0wTI
                        gABgXv7rx47yWdEjBbkH1tbVFRcXF8P2Wa1Q1L5Py+0p4LYeVh4GwyJLSC+RI/octTL3mPL5g0cO
                        yDvMsmOXr1y5onjAwIrDASyGYLBSIAgYlF4S2U6blKzxEjlp4KLD1pf/4eWBjX84b/HunSVvn3jw
                        de6xCSc2MBKJINYSRjweh5lThaq+A8FkNlm2odlNtQFIvjHY8NdoN9n7hGee+dIHXgP6iCNWFM54
                        aOY/21rrx+0ybs/2zs5ONDc3lubm5re2NTcNC/hz6nJDeYlUKtUejbcdvXHTJruoIB/JVAp1dZtR
                        VFSEoUMGobi4GJs218E0DVRV9UEgkDu3qKAYa9Ytn1hcWLw5EPDrlo5Gr0AduRYAZ8DAvli/fr3N
                        yn6LWYR1tPrfN//xx3MuO2PlmLzK1/9Vt3lTRTKZxObaTSguLh5TkBuClBIDB/VHMJDj/2T5wnzL
                        sqC1RCKRQJ+KMoADl1i3XnJXzXQAJwN0Pf6fve+Ok6q6337OuWX6zM72ZVm2sNQFREBsoGCJsaBG
                        AxpjDDYIYsUCIjp3RBHU2HssxBgLJCrYYiyAQGyA9A67y/bd2Z2dPnPLOe8fd2Z3QY3ml7y/N28y
                        z+ezn525c+f2uee53/I8+MMflrt+9aupEQCqxU46bFapwGaV0B5oQ3NbM+ob6mC32wcUFOQyi8Ua
                        i6uxIeCkKaXGSymliCfjEEVRdDrs5YIoBFWVbKiquuWTs86ab3n//UXsJ8f+Gg17oxg+GD0k4pbr
                        lEqZiNUjq1JnELScCBDGudQiwLIDTO6SJE+zRMJIqSk5lgrkICF0cyo5RaKVMFh0wLaLCdWbYOyp
                        E4R2SZD52CE1g6I6p6stNtmqIqUTYkCSBQiCuIMY8nORTTXLHvnw3O6+PytCCZlaNcO2/OBzib5y
                        4Zm0WRZZ/LvjPz+18QNEwoCMn95xY84n9z4Svfy+HSX96b7Lid41hUAe5cnxJuIdX4Fr6pLCwAmP
                        zvBNQUbntnQccN2l740VUt98JooiWuob4HK5QPU43G43hjqqZv76gSuWEhCdcc4IMkqT4mHr78m9
                        9xCJwwdujh5zI/Gaq98ekVfSscgwYmeKgnOTrts+B2VJb+6QTe2Brc57777qOdO5kdDhyyBum5pS
                        6ff2f/5YIvF/mWv+wOV3x41/OVW0t49RqUhlKTQ7loiUWa1WWAhnesp1m513JuUmx4c3Lb1mPwAQ
                        kVDODWaAgvFeGmQYwK13vH2qk7ZPECVtLCGRk9raWj0Fhe737rrrrnNEkUOZJsvKG4YKwwCo6WGi
                        UwPSZFHUPzGVIDMZk5vOipd4Ri2fYpPZsVyMX/b1po1iIBBAQVE+hgwZAkEUvuGcBJnsmlBQPGRp
                        R/OuEoPJ24RU04F77rvzxXePJfYpX6nxXrKSXh/peUcB4M47n3yN88Q0QbAEAVBZsrBkMuFtbmuD
                        qqoozMtHU1MTBEFAXl4eJJmioaEBNosMQRDQv7QEqVQKXd1B9C/tH9SZ5YMNa2bfeHAXkudPf32K
                        wx54iHGjKBLuxq5du+B22FFVVQWPw1MPJmyM0fDwREK47y9/Of6dCy6YEFL8Jr/iAObdssjYtOEb
                        5Obmoq2tAxaLBV63C/n5+ageNFCNx2NiisdDmkof3v2N/vybH85v477ec0v8BIAP3KeA+BX4uAI/
                        gDNmPld0bGnor5s3bxpBExZwzjFo0EDE43EQQmCz2fRYKiaKogiLLHUCwteMuV5r2HzOB7/+WXXq
                        7ClAEMCePcCQE4DLLvvUVVm5bQSQzJOk+C8Z0ydRSnYxhibDEEOUiiGRSCoRBEiiK1PbAqYzUHS6
                        OYxyA0gQ5vyU8lM/rd9yVP0qL7SWt5GQKESNQb/1hj8f53A2XCumPH+GqBXbNOOgrDlaV7++Y6+p
                        AzFN5GyZDvTqQJxz1DXO97Y9HeshD2mJa24YoJIocsb1/4Q6pyz+8/GfH5HIoOfJVyRA7wBz6vPz
                        PZ/c+0jonOXI9ThDRTSWGsWBEtliawl1dxXJov4SRetbY2dMwTsbmzF2rNlzPn44bF573Wv7D7Wg
                        q6sLI4cMQV5eni5zo03WLS//etEVLyMFlcm8Z/WSLPe2Jfa5QxAiUU1LscM8MjjADA4iEAgCrKt8
                        0D9KtZze3dl+Zl5e0VYD6BTEZH9Nt69ta2vBts1D3hHTz7A609hCIul3w0IV8O9hCOZ6dF39h4y9
                        +hoxZV739fswl6mDENMqmgNgnPUYiGX220ibRBlpt1NJVGTGFZUZgCiCMgZ2ww0Pn5xjLa4kVC+y
                        yLFZnCNms9qRTCaRm5e/Psqi53HN+MgkEeZunntivgNABEjXtGqAKEOee9OaM9wWdTSlspcAdnBx
                        q9PhnkiIsVuSJJFz0FEXn5EHIrRBBAgB5dzQCQTkV51roxQRSiqt3KhNXnD8V3ljzvy8Lh6PywcP
                        mNTS4XAgNzcXJf361QHSFoMhUVUxfEu7av+orb2xkjPxAIcYFSCJnAFTvtbiGa2eulNka/nHPNk3
                        QHXVg40jCuNvbolHu5GbmxfkEJGTk8O6OrtQV1eHZDKFcDiMvbt2YdiwYSgsKEAqlUJ3dzfy8vIA
                        mN0usXg3GGPoX9pvf7LLM/2+J678Gki3sgiBgv11u4o45wgFu2DwFCTZCd1IgSEFAho9dKjpZ0uX
                        PrGX+ziIX4EfCuU+sHPe3OYZK9sXWqz0zkQyDIsVEEUDNpcMyUYRiYfAmbAqHgz7Xnnir9808w1J
                        QhRAUQ67/n2KHwr8gAIAfvjAgWdntH1w2vxJRw8bUlmcI3+9fft2NLQehGEYqKqqamOM7XIg9xNt
                        17TfTTq7MjFkMJCXB+ASwJtvHoAF57kAAIAASURBVL8l80GSnpXl9C/1l1RXav2IkBjPObMxQ2zj
                        TH6XcUsjBZcpNQxJthguhwMG5wh2h0XO1AKAMCqIUcr1RmrY34qtmfHZU994ouZ1j54Hk4xR1kOP
                        XfgFgC/IEcT4psczv5tlPdGFjGz1u1ufigJP9c6cKdQVBWRrI7L4/wn/PUTie/DJVfeGAOCLfoiP
                        ObD/MmYQJwXzuu1yR0c81AbE37tdKT8EACUl/TCuFHhnJXD2Cc+8sfmbjaVtjc2wWCyw2WVw6DqH
                        zmb7bpyPRaCwQOztP/xu1EyTZIOlVErMU6GqKiRJMkWIOIehETADydvnvnGxzaHdky8XtAmCpAoG
                        93LOZcJiTrVl+IYPPjiqBQAMZvQoGcI8v3+3ayVjO67rZrdDxk2UMQZBEGTGmAqgj4tjXwL03a/7
                        EpPM9lDRdCPi3KxXyHRFZGZlTFGrB2707N8/NgQAN9zw0slWqzREkGNjg6HGGfnF/Ts0TQMVrJHy
                        8gHhYFtdlSg4PgCLDgCAF6b9qvzKZX+oX7G2I0IIkXWedqWkgG/BH34HopWPHnXyJ99883mYkJSX
                        6fRLhz2nfs1nN89btWoSgNW44IIPOwFgxw6IumZ2+xRf+aKr9YUVEQDQ0yRi9E/X7GFMlJuammCz
                        2VFcVBKkxP6BwO07h1f+6v2G+hQ62R+vqa9rcqSIOy5bir/2uNzoDLRTcBKlFD3tndBUVHxiJCuF
                        pdZaY3qSGMCN174/rl+/hpXRSBLxWAo5Hgq7zYqO1lbv119/jaamJuTnFQAAjjtmPHJyctAdDiEv
                        Nz808qhR8YaG+hJBoB2ccbS07C0oKix+JdKMBx54/srti9IRAeIHZDn6s0CgFYwxiAKBJ8cBUQII
                        NQBD/8h7b+4NL/EnkkuXAlAAriggxGRs714wMnTaB+8+euIpx6REi3FPW1sbdF2HruvI8bq/6D7k
                        uHLfOyMaVgYmxNIajGne5AcA+GAu06+Y/0HSn/gAnwKc+fGiIICgn1xuWzBv+A0UoipI/My61RN+
                        +fOTJqbOOgd47iGTjfx5JYjHDWxv/6TI4Wk72uDRPFYUHkEQP58IjnrOhGbKcz/kEEIASXHKGRC1
                        MTBYZAtULeHo6Iy6ARAikG4G6W9Axee/ffCijTAAoddg11ToZCZttUgQ2ZG/756fwY9Vhswii///
                        8V+U2jBfKDAjEv6eGTjOebgxt8De396/89mXiI6hZWUVHY0NtYM1w/77JPnkkYbkurZHFh/QAKDf
                        O4Bv/e8vb2nb/sTXX38NgZjS1qNqqlEzvCZYvd159AVvzDaNuEyhpCOQViz81oZ+V7cGg0gpThoN
                        76lTXpwbT7bOtTudEYC7KFgD43SnlhCX3rtk9uucmvoIlGgQBAEMOgRYKEdauOl/WOtwZLThyGnf
                        FaHoC0IIZdxgQJpIMAJB6J2Hkh2yYdSoma/deONTk2VZLxAEocBiSc5NxuNlDrtnDaPiibphf1mU
                        cyKG1nqDxPl6zoV6ibWG2L7IAwtev68WgkS5zhmhwMCfwLP3rwgtvPMPL8WTbdMTiQQKcmvuYizq
                        AgxnLN753oOP3PIh5wTTFND7FdAKgC3fAUytgQ4YjDEGQkRQ+o4dmKIumPvYbFFOPLJx40ZYRAtq
                        amp0Dus7Ind/An3wNgC484EJG//6Fzg/XvenSwYNHIhDe3fJAFUZCYwihHsEQ/vIv2jO05z3SFHg
                        ktOvLnr1r79rA4A773x20aGGHbcXFpQhEAigX0khWltb0dnRjmAwiHA4DLfbDbc7B+FwGIMGDYTT
                        6UR+QX5LcUkJmpsbYLFaEYtHSygVO6je9XL31j0PPzR2WQsAED8RAa7DB9yavOtSNRX7fWtrK2RJ
                        QmVlVRNTU79tPdDw9u+W/6EePg4oBNNqIA7fCV3JXLTK4VfwBR/82pvb3+WMh1JJySaKL7/7TAf3
                        cZ34CbjPnJ8QYPokyHUnQ18NMHMZ34YvvVw/yazI/KVyH8e4P8xzbdiwmAPA+0uBh58AKRv5tTun
                        7OPZROBHmeyMDyaEgBvy2lxPfmdOXjkO1R8CgwYOw845Wjyu3FgoUl9iMM3NoIFSocsw6HZCyra8
                        9945e/bu9cZpTxtvOgWZjmRyrn/nDZN/j2kV+UEikSUaWfz/j//iiIR5PzjnXuT+4o7+qd2+N35D
                        CC/welwdjXX7wZjxfirifGvII79vfOi5mfDOBHw+4JG/bj66Wz3wRPOhBuS6PQh3t2FQZSUGVldA
                        jKvXX/DazPrenr0fvzXULMjUGTczpgJVRMYVnevAqec9/wylqVGEEOipmMtisUAAZ9DEj+ofdLyN
                        JQDhAGEaBNm8MTFGwKjxQ72d32I0hgGIkiJqqqKLIr6XJHwXaWBpDYdMekbXdRhcZ4ynpxN62H2T
                        ksmirq/qIRFz5z5yVY6HnM8Mcacg6tMBEXa7bTfjKuLJspkptbDWSZrPE4h9DRB1UKKeCUoPCE7J
                        ll6keH7xi5YVbVdEDn6M2O3zX7uKxZqmM8ZgFWWIjNlSjEaMVP76cHfsAPeBzb0/37FMCcQAiBsB
                        MdYBtnQ15MtOYklKKWoI5BlVU6TCCxfFQsEQqGymbGpqhjBRIBs1g9VL8SGrrzr3xPb8iYgDwEef
                        RIupzAv27d1W5CAxgGqDQNSxnAkfaQlxd89xT7eavvrX37WBAwt9j78QDnZeMWTgMMSiSVAGbNmw
                        Ca2trbDIIqxWK5xWG6AbELiOEUMHoWb4sLiqqoirCU97c0PI7XFGDh2qG+xweZd9umrotaesPavz
                        IV/fk+zTASL6FK77lbtfmR685avKquoLwVjyyy/3vfqX4/7QhuUK0jEDzL0y39GxM5DIkAhCAN4T
                        1UhfGz4ehB9BAgXcp+D3Y58G8VfK3MdVKAQKMZemrOqjHJpeQ19CDzKZ+kEYfBw+H+D3K+BcASEE
                        ih/YiCWRs84bkjfkqNBggtiUEWfrAynFQBDuBpcawIQGgHZx0HBBbmGcyiKisSgY58gr8KKtrZUC
                        Wmko1L2XU7GDcs+G7btmvAsAW5LrU+3rTkw8+ljv7wAA7rig2Fa7MqBxpuno81tgjEEULSLnejYF
                        kcV/Pf7zIxI9tRGk5xkHAPyZDwgw8mw4fjbuuTWCKIJpUTvARU1NPXPvdbc9TTYCM6YAYzeas3et
                        fG7nms8+LY92d8LpdMKbK6Nfv34o95Bzr5s69JNHn1nmuuG5v7RpDBAFEUfmTPvqDQCApmuQRKmn
                        1NFggEVYLQKTmMbBFt605kzRdWA2IbGzGxsbYbAkysvLdcKTm8NPiacsab8lAsMAkWSZc0MFN9sY
                        QUl6l4/kEkcUc5oy0RTwgTPlsJl1HRDTYd3MzTMjF236TtDDCEWGSPR9zykgEoGmmMoEYtqiHykT
                        VXweXJcMfXWcx9YxF6CgVD9dEh2dqpqycj2yXFOx1vLr+a/u3AkM3PrmzbLY/XORRStj8agXRhfc
                        ouXy+Yv9S82lmdx43vy3r6P00GPxcCtCoRAKC4tgo+UXcZ1E/vTIZZ/sBHTzaTmdslkNyiapOkMm
                        tWOmRe6749W7d+37250CMUApRY7XBq83V6WcLwZ4Mdfyl91172++2L8FlupjkFr6Kvrt2vHaxVzE
                        CMJC5yaa91gFQUBuYX4njduvXPDw9e9xAzoRzDoQwwBmz35lTFlx8O1INFTWHYjg0KFDSEWTsFqt
                        CCe6EY1GYZFFOJ1OUGoStNKSEhx37HFxKgsghKCxucHusDs6dGhBNUJvucc99z3i78MTM6N2eoIC
                        wA8FvvQvwk8IfD4f4FegpOe9HJPll/jqPrLh5gXEM4U+inklE/+362R67ivp45v54SlIpzP6fL/v
                        5wvSA/jCewBiXqzsuuvun8a5NkSStJ8TRlIAqyawHAJojDNhH0C0zIVOqJEUJYEBgKEboICFca2E
                        QqoDhG7J6Lf2nocv+ZhIIFzvQ6N7hFXTkzLtl+TwzOBSxSLn2vOFgfGAVqOkeusejviVZeIT4o82
                        u8kii/9/8V8ckejF+eNeuU3kcOa5cqKHGhqHOqze5ykP6QBQMgV4DoBvI8Cann19w2dry9WUAYfV
                        BlkQkedxYkBB/kfX0QWfoOQc3PA7W+S5dyT7FVN4XAeH9ANl15IoQTd00LQNn0WArPNJKgcwX3nm
                        Cqc9fwQ33Hs1Fjy7u7sbeqobAweUNcmp1It3tM6PgGuAJIucqSq4kRZIMhWSDP273P0OB+dplUcO
                        TCgMuE74xQdjJGt0CBWYBwC9Z/G1j3PO49+RsuizDH5YJCIDSikMMKhcYzR9pxYJoSmDMSF9o75w
                        9l/LLj9q3xkCJ6WaIX3OtKiSm5sXTyQSDoPZXg0Eap8tLn5ok38ExPwTYck/dtSqAs/qWTt2bvcS
                        QjCg1AlYpMkAlq47d4x3woqtwTtmvT3a2i/wSG1tG0rzgTjVEA21w1tsPT0Ys72wA9AJCIgfIgAd
                        foBxQzeY0ZNu6HcSXDNO/d0dyc7OuWqUwZVjhcfjQa7X9jE46eI8pF74yi3X13zqlSADwbAZa9m6
                        ZVupRLsnwZDGbt263Zps3oq8vDwUFhU4cpL2Nm6YOXXGwCiFPO/WpZcY+raXtm2LIRaLwWtzgegM
                        qXgCyVgcDo8V+f3LQGCSOIvFJDr9igrgclhhcB1dXV3w2K17CVh31yH51w8uvXmveXQnU2CVOcD6
                        CXzgfYi0SSL8ilmc0PeBgvgJJgF0Fbja+wUz2gClj9+9ggwBMI9jn+shc41wH4fiN9eh+Dj8ipL+
                        jnLkMoDfQz445m9FRaV7ht98beS4OUJyNINaDRCNQO4Ak1soBAMgDRbZERElU/U1Fg+7OU3lgtFu
                        AM2pVMIliEYxYwgRgq8FQ35z8RNzPwJMAnvPw8CMqo02YGyc8141UvOiJTA0HaIoiea+pPQMeTg3
                        HkhOV/6+Ui6yxCGL/0L8FxCJntxlpmRKAzKBW6BzOkoJi4wmRFA7uwJut8O9FYzlXnj2hA2PPHHd
                        0S2LHv/msnWR/tG6z0c11u2cEolEQCmFVeSwSAIKi3JxvUOcihlVEupC8qIGmapTEJSgpG9EyuGC
                        Q31CxJmtk0WJpripmahyqBzATQ/88USvNXk+NXiECNGf79i6B7FYDGOPHgoaY3Pm/Pbut7Hkboo/
                        jvRgO2Lwm2ZSxMd7ijDMMfFw5cTvPUoGMOFXb5xidWiXEMqHEkhJcNo5a9asNZqmfSFJ0rciEOZ+
                        EDB2uEEWIYSqqsoyhZv0sHVPogIljHPg8pufqh6Wr08FiC5SDKdEm2Z15O1NJBIDUqpn/t66KW/+
                        +Y85DQDg90MPnAbdqe0Z1dLcUBaLxRCNRgFDxsgRI4avO3eMd8JTT+GO36wcZetX++b+2iA9VN8I
                        rsbBOYfDVQAiRXKkhtYGwMy5A9CJn4BxzdwDSiFOgf2CId8UzDxtj1J3cP90EuOIxVOoHlKOmpoa
                        7N339WmyYHkrrzX4fM2IlRzlv1YB4JhTEU8AEO11w6GpJ236ZotYV1eHUXk22AUKQ0tZqTVRkCF3
                        P7u2q+zWO987MxpueFZlBMGubtjtdjQ3NcFmsyHP6YTX60X/IQOg6zoO7N+LeDwKgTqQk5ODwsJ8
                        pJIJtLQ32x12R4hw3nHu08POHBI4NzY3/34Hn31bDFjF4AeWYqnMkbaTVgDiVwD4RXBFhx/wcZ6e
                        bhKP9LHpIQzED5gxPAVQOOA3uy64j6eLMLlOiGLO04dE+NIpFQUA0q97IiAAMHmpjJOnq5fPXzHU
                        EW+fSM7rPlkCqgFupZAASCBwJDhzbLHIMhs0ZAi2b9sq5eR40N0dkJHUCBUiRxNKogCjoMwFwrsE
                        XrAy2p23seVQbuS26y9OnXhuIInH70j/LFIcAJ49MDrODRUXSRZxOWAwzax9IIRCkET0pCw4x+V+
                        aExvUnt104FpRBQ7KkBX1eppYiEcfrfJIov/IvznE4l0kdT3wZ1TX0ggOgGOZDJa7bAam6GLf3O5
                        nGCEWie2wuFpqCuxCLuX19XVQdR0ONxuuOwEY8eOxa/WrSiMpFKia25YhTecVBFIAaDjmyyur0pT
                        oR/aPJEQqptmTwDMVMNt/hfP9NjUCxisnTJhRbt275a1RBI2SYZVsD45fsn4lQAoDFXHZ3ICAAL2
                        fEeYu2kVEAIAIqwWuTHp+/O3fSK4p1cf9Iy/8NPzrE59ukDpsQAoOEuAGEMcDke1LMubOOdqhjQc
                        GZ3oC13XMXHixLwJEyawdevWdUIi6I1GCFRLt7WNufRV79nV0fMEKlcDYJTgZAKSTCaTiMRKL374
                        wZ+9A4DijwSTL4cMnzkQCjSVRwhBJBJJexpIiESj4yZMmIDnHv5Dpa3fkE83bNzgSWpO5Obmwmpl
                        aGtrw4jhNRuOfenuX01atQoz3yXWEj+SCjc7BOhkSURkpaxvmBLXViLu82+4KdDeOl3TNBQ6vRha
                        PQiyxcC+nbvhkfMuuXnk9R9jEboyp3DzM+CjfwPcOv+TMrvYdlJTU6NYW1sLh8MBQTBTFLIsQ0jS
                        5NWnrS0oGbt9/KgicrWuRc8TBAFdXV1wOBxIJpMoKioCAHhkG6qrqwGbuFUzUqN0XYdhGEgmk4jH
                        44jFYujq6rLn5nt1S0q877rFcx4AzEF7SeC2GGASBg4F0zHd7LyBAvjXUO5bxRRF0f0kTSJgkgXu
                        W9WHPEwTuc9sWeQ+AH4OxQ8z7eHL+JgAhJh1DGYtg9J7efHe1CHAe4oh/H6gqmq56+pzpUrrmNhQ
                        Hl1ynDmb4KCE2zgXOjgjYVHwhCRJ1lIaIVRgFgCora0FQFh3uL0c1ACBUcAN+RtBsHZDH7hiycPn
                        /QUCQCgI08EJBda/AxsA1C2FVHEMGJZb0lXXJnFY1kMYvl30OI2I4jKm6YcVWHKOyVWSvIpp6o9R
                        ps0ii/8G/OcTif0TXahu0jHtgLpjOeDn5qNDPs6TAlipLbR+PpgRsQCIjsj1uvUd+z8f7cz1XL8j
                        chOKxk5MXrb4y9Ht9i//un3bN/CAIylG0b+sFLKFQCKp+4pXr+7ou7o704PmglKYqpIMPTdTwzCw
                        UJJEACxTZJbinBWvX+kKnHhuDAC99A5UVNjtV1Hd2t/uLNjZtPfDMwUeQzIexMknn4zut/U7J2mT
                        dIgZQSvorEbV82qg5oKDpSWhOT9JBxjGLRni+XLunhBAIU4ZZ9ff2WA6iab9JM4vguv46TvOsNi0
                        xwxGPUVebzwYDFIOXSSG/Ofo7n6ruG4O4plOAw4GnWQCLSJ+dt/lpW/f8fumo6952ntUpDTvhBN+
                        ss8uF65a5H9lXzfvXHn/wlveEwBoGmcCA6gA+11zhcuIXjCQEINRIXVGKhUqslhs+7VE9N6HH/zZ
                        O9xHAD8YCOgqH1RFSVfyzx+O/fs+gy5FEecJtLQZGFBRhpdDN8xQ3Ttv37vpTY9NZ0hGmqCrFjR3
                        pzBq1Fho23dNm1QH9dW/PV72LEeLAkCpg+yv8KlYBQZlSlIksM6767Wb9u/ZfIOhcgwfPhxDCr1Y
                        s2YNKgYNQLlU9rMrFv/6ncy5TgDYocIengIAUItiTmI4Ahdu/3It8iUKokVxsMuF0QPKoBNvZ7vD
                        ffrwE+uPMyx8XkPtPrvNZkNnaws8hKEzFgQA6KKAESNGgFDnikjS/aZLF8Zs3PzJKINQqIyDpeLI
                        s+bB5rIm7U7rXjUef1TdGvqgJ8SmpOsQJoOCK4d1R5gRBTPSoMAMLmRCZUqfJL8CAH7oZA2hfFVv
                        PURmUUQBsAaUrwLjCkCIX/T5oPM+0baeyET6O1fdoI6wiPvyrmWbxhIeHg4wg3K1ChCTDEKHIEMz
                        DKOLAYIgiVGrV+RWWYLa2MINplPDUJ0plTJCSYAz6c8AdiZT3oYnnrn8wGErggHOwTOxgRPP7UgA
                        QMX0dKFnTe9+kj4Rhm9nIAUs+y4tBwKsquXqd0wG8N9wQ80ii2/jP/+6L+juecpa3mdyAFsNoILC
                        Uv8zcBptb29HUVERygaUBccefywbNw64754PBxZ66h5rONgAQRDgdrth0yna2tpw8onHrjj3j8IS
                        LPzxm5IuVPxWJ0Ug59zUOQ+jROCguXJ9mTkPs3Z3Hpge6GxDJBLBoMGVsMfFC3dviyZAAUIEqusq
                        005SdUDofUI84o745dwDoeIrz3O1vrAior+zIS4SUFUHk2XQm677YOLoqzpPSunNdxNNhiRJaGho
                        sLscnu1EtSytXDbj+Vt2IZRZpGEwUNF0wuy7nrfueKkJAGKB8WzgsHVfhUI67DIYoXq5nUoTZ962
                        vPaF+6fu5AwYVgLX3FtenkIEXk4o88YTHRdLkgSrxb5JTzge//Sld9/qIzdgHi8/KBQwHwf0u7qq
                        WltbIVjDyM/Ph0gJmpqaoFs/WnzgwAGktDAYYxg8uArhcBjDRx3LxJRw95zli+sBYNKkNzsAwPZx
                        lStx2sFE78gL8eob3x8abt+1KMfhwsCRVSgpKcHm9R9h1JBBy4dH8xacNufXrVCBhGUCtfF11AaI
                        42SoiVJzQ1XH35QtmzaJTqcToWAALpcLQwcNBSEEoXB3nsdNLjOIVrB//37aFWhFLBZDefkAAEBb
                        oAOUUowePTpJks45bRtOf7OwsBDGoFcf7OrqQmNTLSoqKlCY74Hb7QbhZKclLiwxWlOb5n34cBuO
                        MyMQvrTgE1b9oBubeb0cMYCmsxwZYtEboVDM6I0CgCu9nykKwNNaCiaByKiUmwu+ahYfYZU/HAty
                        aDQxDC8lmouZ9RRhgIadrrxD4UiX3TAMgVJLhyBIGmOpvGBXowuALkMIgNJWGPZGgeTWa4mihoNv
                        ntG8ot10GM0iiyz+3+M/n0iksWM4sNN8mXZKqGP5OFfSWdxFqTo6Pz8f0WhUlOz42+kTzsTdd795
                        nE0O3Lpt+2ZPOBwGiA7OOVKpBCorK2FrCd5VtfeBEEjmoeVwV8ieXGn65gp6eEpAg85Iuks9VQP1
                        6pURRz/+/nkiSR1HDG4DSYw6VLcfyWQcDocNA4r6P3+d78Y3cT9ABFMGGgAYOIy0h7UpRJW2oeZp
                        KXACBF5cGRNf3CGrRo1qGGAXX/yHygXzU7MEMXFZe2tLUUFBAaLhMHJzc3W7zf02Yp43blv8yz/t
                        WAxZ6NO1ATNA0LO/BASDZ8737H12cUgkEO+6+5MGYsiWnJwclUCbaJHdq4xU1xn5tG7/2ZNaOz5Y
                        WxyaP+/5GQCzU0GvIkQf73TaGWfiR8kIeVJ8+Or31vmuBvwKJgN0Vbo7YMe0zDkDKI2UxRNRSCwB
                        XdWgMY59e/ZDLygARwp2jwNWqxWhWAgOtwOpmPGL2x6c+yf4bsU0P8Rl/cxI0caPgeGnQc/oPQ95
                        EhYhsfqbcDSOSZMm4eDBgwjsaEN+YV5wSvD4WyqfntCEtyChArDxdQyA/K4KVLVAqCyH4Z//yqw9
                        e3ZN27enDoIgwOnIweijxiCnsB8KCwsBTUN7e3tRW2sjNE1DTo4HhqFj8+ZNGDhwIEaMGI7iouJI
                        pNs+9d5HZ3wEAMqC37725RffFCRSMQwcWAlZllFROQBup3tn0aYD51727jNt5nFZbO4E/EDfEAS+
                        /fbIR++MWVaGtx1JLCZNh3zyS1DT5KHncx84FB/g9xMo4Ok6CnPxV13wZtmca2vP4EiV6OSJARRq
                        BTgxAJJgnCUFagmKkpSyWj1Rl9OGaIjHicDtnKWIwTSAIAxu28dJzj4aH7E91j4i8tQKWzfX0wWd
                        T2XalOtkziu+p/gxq8+QRRb/W/jPJxKemPlUNRU9TMIEx7D9yyVKEmpbW6t18MByxONxcEM+1NGc
                        gCx3XabrqaJYLAZZlmGzO0yvDIlhaNnge2fdPWerqfP8A+tPD4EZEnEnTzGRWuiCdE7WQiCP/1nE
                        dcbI15/nXGgTQByUaKM0LQmBGxBEYOyYo7YP2ORWIEOEAX0qhvcoVprOlodvBOMMGbdKAkDnnGXm
                        /9lptUVHHR+8Px6P/NysMbAgkUjA63XXM52sIhHby7c99MtVAFCjQc3YnXNOIIpij8Q3YxSEEOx9
                        dnHIMIB5C565LpXQXTwtjz1oYLna3Nx8OiDu5dwRHj7m49HHTOqcywx5N6GsCISXAkAyLs8MtZV/
                        9sTLZ+0HAMVPoPg4Vil+lqnoXz4cuj8zgC1gkcLcHAS7gVh3DCAcsVgMKDRQUJgLwWa6WkoOe5Kn
                        yPwFi+f9yVwusMxnDkQz51W5hi8+EElfBgABfj7/0SebmpI44djjsG7NZwiHgxg+fDgkRB+tfHRC
                        GwDgbLNQN6EGDche6AcgxwHcOe/tk+008ktKJBQWliAWi6GgoABDh44AoSIkQcKBujo0NzeDQEcq
                        lUI4lYSu67jwwgshMulVy6Fq/+y7zWMAc7fBuDotkYygsCgXaioOUSIADFji8F/27tMtwDM959zs
                        yuiDjCHGjzBr6GmkyKz4MPSG8RUlrQfRZ0U8TSLmXLalWnbX9buNhAa4eccxIMgn3FXPwUIghsE4
                        CQE0IQg05nE7jFAkIKiaarS0tlhAhXaDk30clk4iFDa2Js7du/wJazsAmP1EwBMMMLhZ05ORI+Gs
                        4gc6KLLIIov/DfznEwkACDkoAAwfDuQDQiD9dOv5W0KiSFV6XE60NLaAEAKRCkUvPP/kaUTK7VST
                        SbS2NWLw4MFwux2Ix+MYWD0oeP3d7yhYeOMRDz2HR5J7ntyJDk57n+Q5J9CZ1jPz1F+1lQ4qf+cm
                        AdxjtdraUqnuyra25pLdu7ahvLwchcUFsLUmfz11xW+aAIBDxzK+TTUMA5xzCGmN6UyqwZSh5j1t
                        jIwBsgB61Q0rR+S7ms4YfYJ6MiHGhGQ8hpamRricduSVljUxVX5Bj3k/W/DgL9aAANMERV5mKCqH
                        jlP8FvFTRdXNfTTXJ2S4Cweuv/XP4wodqQmhaByF+SXQdR0tzYco4bReliw6sSfvBFLRwYOHxPbs
                        2T2aEO5lHG1awn7bvi+Hf7hs7cRO+MzBfudwiFDMMM/qSZAnA2qPwCEH+B1yk9NpR3NDFAIHQtEQ
                        JkyYgLLCQmzevBljJgyCw2pdcStuv4AsJljA50FRFHz8cb5DUQIxBUCUOHqYl08BGq98Y7hE8dPh
                        Q0d2bN66qaC0rISV08IOzvC72X/4cDHuu1e/oSxXfLShSweA0y65xLH+Tx9A7YD8xrvrx0pi2wwK
                        d3c8xpBKGphyzs9AiY5YNIHm1iZ0dnYi2BVASUkJcrxehEIhFBdXwu3wvHPb3Tdf8OrIba5Lto0M
                        KTAHaeJXqM+nMMJJ2zHjxxR98cUX8Oa6UFk5ALYoxl7/XHQzfGmC4AcwCRSrwRQOKH2Jg4LvIAaH
                        I5MJ6RuJ8Pl88PvTipI9aQoO7kurWft3iDdctXuoaGsehmvtJXNIaBylGKSD6ARWXYRd58wSAGQb
                        4YcsMNBcXjYgwRhHd6gTke5WC+fJwoQaC3Lufq+t7YLP3nijvA0AKIXMGNQ3HjObaJBuK+0lD2mZ
                        api6JrJ8ZOQh8z7bP5FFFv9b+O8gEh05FDXA1BrgyT6Ti4M5LsYbRrndbrQ3N0MQBAjUcTyVMZzD
                        sO3btw9Opw1Wq4RQ2BSgsoSNXwGAIghUMQz2g/aVR4ASCYSYQ/LkK74pOL1884Mg1CZAThpJvait
                        qWHwvn370K8oH1aRYkBp0QezF9yxKfN9Qk3BKUEQYKTl9xhjENJulWnTK9P0iwGyCPE3N709Jt/d
                        fKtA9XNg0CBAkUwmEQwGUViQB6baHk/G1C/qV7u3EhEAA5YxRSWUUM4hvqSAknQOp49LNwBApJDn
                        Leg+PRQKXuDx5EGSJESjUVRWlMfa29pyw+FIpc3m6LRZHRv27t3tJYQ7AbgM1fbEogdn/skHqAQA
                        9ytQfAoA6OlcO500CWpPFWBaatkidA+Ndnci3p2AU3ahICcXLqsdwVArJp48HpAia/O3br+OjDY7
                        EjIy3n6/P2YuiGPQfQhBMTsIAOCO2zuvA4BPPvmo4ITxx7QB9ABhkf3HHpr0qK3hdh0AxjYszuSw
                        rOv/9AGWrUDuxq8emy2JJRwQnYA2oKqqCkOrKyFJEg7s349EIgGHXYDHaUVl5VFwOd1NgE6LCvIb
                        ENaVuQtv/nA1VtNLtk0KAYclJmQAyWDQMrUoh9+R43WeMWpUDVindvz85+7dDADLd1a5pi4/GEuH
                        iFjfq9APoEeb+kfbR6bN5dLRhvS5SJ9r87ObOh85HtdGps0RjImEiHZwqBypWgCUM+sBgCQJLLDI
                        9ojT6UE4HAGVpFQiEctrbN5fmb6C9+q6tJ4Qz4FgsP/+l16a2iII5u/BMADGe9UvdQadUEI0jXFB
                        ICDE1HzIxNo4z6Yvssji3wH/HUTiu7ADsKncHgp3w+l0QpZlhMNhOHOsJU6HO9TVHS5PJqIYNHgg
                        UqkUDDD0Ky1df6Ny+3tYMh+KBJg5i+9ePO3x1OAQiIUymFGIzCA87aeBkhNHbTkTkHVAdxOI2Lt/
                        28SuQB1KivPhsBIM6FcEa2L/bbDIIpimP33pGO+sP24O/uSMIZ6/fnQgRPuGBXrWix41zZ+dsajo
                        1nmDzrPKXZelktETbTYbEwjxbt++zZqIh3HC8ccyu831ZDgcLm3dLO384+azgxOKhrrWte+OmDUh
                        GmuGJMrobfdjzOz2oOnui5tmfjPcQowz2kNxFOWXoKH+EIYMGYK2tmYPYwx2m+sLznlK11SAI6Xr
                        yfGiYH/l4D7jfe4zBw0FgAI/FMUc2acD8tLVwPRJUPs+UV99x/JRDe07zmhpOwSLZIVARBTk5qG1
                        qRUTTz5NJSC6Fqm76vIVrzdd8fbrPXl/BcRUYO57ghQAfmDO9e9O5uA5qWSy4PjxE/dSxNwCJ7tv
                        73/DHCjeJBl0Ovi+j3AZZogJgM2/D9ZE13s5blviJHChEkha8vL6dUc6m4Z7PG7dZRfR0HBI1PQE
                        JJnA4bCjsLAQsijupIxuVnW2VWy1r7h56c17FRBTCyujx4Cejomkqf540/q5A5dcNOYXR9+frN+8
                        eGHV8noAePzyiXnXvbi28/65+Y7bEIj13S2zpdNciL9Pxeq3kWnfNDna4fbewIn5AUfl2I3OW65t
                        Hmag9ThO9JGUsgqAl5meFjQMswLIBiY0Wi0WlVIBgIB4PGhJ6B2DOEtVUxqSJIF8DMPyYjJZ9dkT
                        T19Sm4mWTShcbxMIYGhmxCETXCDpPCDnnDOmccDo0SoBTKEzU0EV+P5aiCzJyCKL/y385xMJ5WAC
                        9rAVtx0xfTmoILusGW2EtrY2hMNhiBYRIrW7gsEgNE1DMhnDoUOHMHjYUIit4rU9IymM9Kj6Yw01
                        RHMwNksOUD10ywiLVT+XcFeKkKSXadHhgdY2lJYUmhoJLgcKDMucK+9/ZjvufwbgHLNe2RTknOOj
                        j/aFCCEyyzhc9pHmyxAVQkFvvbao2iqFL9O11IkWix2ECDhUf9Cak5ODETXD9lJYvorGQkPVcI7/
                        pbVXdb5IrsO69t2RzLIYZ4iQfCEKNy3JLB8wxa4EyLOu+rI6v3DXQyC0uqioCPv370dV5QC0tLRA
                        lABJlOt1joQkSqqm6wCITrj7tqY9g/70x5E/aQAy+gVmiJ2kB8GXFJNgPPcOrJiSbqMlQIG/a9pb
                        az5BONyFsvxCEEKQTGoYPnwkOJLUmYLieujlur5H3ccBfyVk1KafdDNsIq2HYFdbZoDQCm7WmSQI
                        p3sohFZc5U0CAN/3kTWzrI17YZ14Imx/+7B9OPTEyTDFzaTOzpYyC2xfE5IaduDA7qpgMAhdD8Nq
                        tQLEClGCzpnWJCXIn+f89ra3ey5N+KD4FNOzIq0u6ScEPs57KMDiX82NAJhF/kDMBiEfMNK/NogX
                        gduWBGLoI+6spD9XkCYkPXUSwLciZ0p6ah830BPzA47xF348ZM61TWMY4mOIwAZyYi3numETqNDJ
                        meUAKFoBLnKDRgDoud5C3Wqzorn5kJVQYxBAGRdoF4VlnYhRfkPdHPnto7d903uBAgUF59k6OlYk
                        1nWcmAAA8Ygxn/eR2czUFglClhhkkcW/K/7jicSnnnNyT7np3a6XVxZYLzs3oAXAtYnfwC5MR1JY
                        KlkpOGoP7Eco0I14PI6qqioIhOYd2LcX/fuVQGWdECwEJcV5a2ILb9uJZ282K8A4A0mTCEIkypjO
                        COnbFEnN1INAoEFlWtpOmxPguttfHl/k1m8HBF2giUm6oXoS4QiioTBcgwqRiEfQv3+/yJV3H/04
                        7uMAkSmYykD61kJkSASozsG4YXpDMAO4fc6zp86/VS2XrKkrwlGc6HQ64XHa1aamRrmoqAgW2drh
                        kOSvkqkkqK59wNeoOznjzNB1gHIIggAOQDjFIi5ZlY84Dmpj0k94QnpwuvkClHhKdt4NQvoDFA31
                        h1BRUYF4IorCgqLOSKzT6/Xm7DcYQVewSwSYqiUsz9/rmP06hqfFknwKDtceSL+ebGoUzJiC5EwF
                        gALMv+uZu9d++t7tupECiAFdA4qKCkFJAm5XDkY01Y499csvG4DDbJ1FP6Dj131KYvs4RV0W2FLN
                        3YaXgDkk0V4HwgaC45BbK/1rZvYPVsNx5iTEnngF3oY9j/4KDAMEEBWUegGAstgArlXfl0o6myTX
                        zkuZkPpNKB7A4EEm9eKqDsJZg5Skr1/424F/ySyXwCyOVBQ/uGKO+STtbtXXAwMKAZS0Emv6+ExK
                        a0EQBeCKSSD86eOXqXlQwOEHASG812bjiKhDZpFzfvPquJuvDU/gJHwMh3aCwY1+BCRIibWbcKFb
                        Fh0b8vJzEY8nwZGCVbaiO9wJw0gN7AodqOQh4RsC8VPKil7oCpL9L7xyaifTK1Qz8vCTw3+QHOho
                        X5Ho9cD5oVqG7/v8yFqILNHIIov/V/iPJxKn3Ph2J4hIbDyQfPLpkQ7MQlJuADW8gF1sOqe1uxua
                        psFms5mqjRDxzaYtVqtNRDjSBa9HwIQTJzSQrugdisHVTFElIQSTiSSungQgPdEwDIiiTA1DY5RS
                        s44BgNRHCOryaz+vrigI3QzD3QGilySSMY+madi4YQOKioogSRJkWYY3npgB3CIDn+vgKuOMY95V
                        /VyLn2+N9BojAXq6jeLss9eWfPDBxJb5Ny870e5N+AwjNZFSSXW5PA02mw2xWKiIcw5Ztu3nIOFk
                        KimDcE/8U9fL922eFQRgFm6S3pQMW6Xppc9JRsMMNQ5QUEJkLcXVW699e3RuTdc9hOjDu4Ldlaqq
                        oqioCG63G9t3HoDd5oDd7lrFGENXMCQDJKolLK++9OC1by9KO0Ryn2IOZmnDJ5/vsNqL3toTBbhr
                        4aPbNm/ZMrzhUAsGlBaBCgwukoNwKIbSgSVwubw41bGxE0O3xXDBYac/Qyp6q/uV3r9Cz/ZxFMYg
                        SZZjTE2J4CQsgLbMmj2t5wn6zEmI3TSnfbDV8tojnCVPEiXpM4ML20Wm92dMHQNq29Ky77TPX1yB
                        pkoct/Hyuxb+LMdrK/K4ZVBKWoSUfbslIjx5/cO3vGOunohAich5OtLSFwoA+HpJBIBKArmWm4Jc
                        ADBzCqx5lRMd9z2+tpPzPrv0HW6svI/rp+JHWtoaACDffN3zP2G8/aRbBDaJAjJACSGwEAiMcnEX
                        BwmCg+a4ChosNgvi8QSisWARSLIqFgc41xo5SDsz5AdF4m7bU7trz8qVd3QSYuD5PwA9A3y2jiGL
                        LP7j8V/j/qkQIk4FMMKcQG+4IzbcI7/22s7N64YyxpDsTkCSJFQPrkFDQwP6DfCgubkZRUUOFLtc
                        v5p//5JXAMAwdDNHm265JJMFka3SdAIRmqZBENLtmNz83EjfuSdf8UFBsSPgHJQbmQ2ASsxZRGny
                        ZIlYvdu2bbW3t9SjvLwcVjGJgVUDg3PuntMfmhZXLpFkZZmqAsSMfJitnNBZ78DLGCBR4MYbXzjV
                        64ncTKlxutPqWZ+bl4fWTh2JRPdosKRHIPZ3KYn2B9hAQ4+7pPaio+Y/e9VWcEA3NHO/RAKDGZBO
                        lUV9laEDFGLNNBk7l7FJHJh42x8uFa2hq0Ri9XSHAiPaWlpQXFyMaDSE5uZmHDP+6E5wulmSAE1T
                        qQ6q6gnr0oUPzn6TA3qmR1FJD5aKokBRlJ5HaX/ao9XHFfjJ5fKCBaNe+vrr9RcnUxwDBgxAqKsF
                        e/bsQb51AAYMGICh1SXwenLbzqz46pTqUWualo87mCgA2GRfiQylJZl5CPcfeUEQYN4djz4vcmkI
                        ASsUCArb21vdFfn95t565jUvIgca3gF80dcXM0SOr6wqCx06dAiq3jmeEBI3RZisL372uxsXrQsg
                        lhnCf3PZlJK/vPxuZy0H++CDiZ4vzxIiClapPSvt83ObOa/KVbL4YKTv9mVqURRkjpNpw+1Hut6U
                        937uh3ncfL2Hrxf+njXKHFB/fc6WkryKj35BhNQUQngJOE0CUAFqAIYThEjgiHPQoM1i7yosLILV
                        KmPPvv05BKwYADgTdhJY1su8dLuhy/XRlmHBR/9UFRBFgl6uZpE3BqvEHOtIu8Vm8P54u/Of+wEf
                        GZEQfuB9Fllk8b+N//iIxA4KsUaHLr15jgdT3w0BwM2PrB/rsLReo6W0oYIgwG63I9YZBaUUgUAA
                        5eXl4GI3LFYBxYWFa8nq1R8CAKEC5UxnhmFASBMJtqpXRpdSCrPgDGBGesQQCCZf1FpQ7HE4h+bt
                        fYFA8nAutFAhfiY4iUQiYfv+/ftR3r8QnHNomgZnynUH0k/Td72mqoTIlHPOeNqTwzCA9GogEog6
                        gz5/7ktXeXNi1wmEjCgprlrLmQZZkpBIBIsikZAnx5m7knG53iry2kgiMNomWJ+f/9RVW7nOQEQK
                        MW2wxQEIlEJP75dIQMGX6SDAcXc+e6toJWcSYtFB9OGiKKK6uhrr1q2DxSJixIgRkETLVk3TqKap
                        DIQPVSP6rHsfnv0O96XrIdLHyu/vNXjy+/0i/Iru4xmBJAV+AnHBgqOe/WLdZxc7nU4MrR4EQggO
                        7t+MAeX94BT6w+7OQVtnN6JJrWh7/8JTq4eUvj4Vv4rApwB+kkQ6vO9TeE+KAECagXFQqg6DIUog
                        rB9AdZfLhcr+w1IPvf/7XwIdVo3oI2oPBi4VBAGNh3aBUgpPjhUupzsMkGf8993Yw0960gbKuy34
                        vXlqZDnFFHyZLig1hZsyMQEyGRQ4GAN6yjUOQ09bpt9MTPREFzL7oAAgSsYLqydVkUljjAWsY8/4
                        0HPzoLqL5iAyuEDQRxpMHQHOrJyDUio1gpMkB8IEAuOctlZUVDAAqDtYh0MNtf0BQIDzK8Zs70go
                        /pq9dvamJa1IEBGirkEXhJ7dgQILAYBrkS+M9R7UgdJ/kcbDkW6a2bbOLLL4d8N/PJGo0aGvLA7Y
                        ZnYMj78Y+EJCPjRHatcMYljGaykdhqYioWvweFwIBALwpHTYLXa0BOsxcGAV7MmmG29c82kHOAfX
                        VGb2oBFwUDDOQNItEoYBkHTRo2FwcAaIEgGhoCMvKdYnlb39ewKLw+F0obGh9myvu7ABgJxQo54B
                        laUQiQGHy4ay0kJ4N8oroasqRIlSbjBNS/W09x31y1e9m165JJhZJ0og36U89aAkw8U5CUqSc217
                        ewD5uTk4WFtnAXI2O539/5pSxfWCFB6TMrovSSSjyNuVfxdkUGLQwwQwMm94+k9jYD+fjbKaBS/c
                        RAkpAEhcN+JnppIqwBh27tiKvFwPcnPdyHG5NmtaEiDcy6FTrpNX73HPeydzkXEfcN7HBY4V6wIx
                        06PBdIjknOuEZKIRJubPe3Hu3j27L2MpDa58O2w2G9ra2jBm3GgYhoFgsw2qzhALhxGJJhBlOePh
                        tP8JQE/hYsZESlHSD+gKkB75KDgYu5PVE6iDOefhUHe0WJZl7GzcfKPO1Aq7TUQyaWDokGH7G5vq
                        q/ML85FIJOB0C18od38w0YfVjOA2kftMwjXt0yvylp3yYueoUSM9W7duDZHJhPFVXwUJiNmNkXHC
                        VAC/HzJ8UH0K+nSWHPkic8wUwK/0KkdCoUDaQ4Onj5nPJBDvVsGln/XHcbeg5VhB0I8FjHIdUoKC
                        uwGmgSNBBSFs6AbllO0uKi6CzWpDbV1tFyVscF3dzgShCHGCQ9ywvmTB0M8WP3H+Lj0FDgrgIVMU
                        iunQQdGHFvUiH4F0ymbt9/wi/1URhGwkIoss/l3wH08kwIH2o/ItAFIn5p+AuQ+sPhMGHwpQh9Xq
                        QH5+Pvbs2YMcew4ikQgmnDgQVqsVdocMSo2PbrzvcVPDgRCzmhHoKUYUqSDqhjmQZIrKmQFwRiCm
                        paVvnf/RFZQyC2P2fQTMHo+Fz/d6C3bDsBwYNGgQavdtP8NisYiHDu6FqqogjKya+tZlTWm3cwpQ
                        JoqyObBzYNurl8TIH831zL/z5Z/Pv1KdRLhsoQQ5/Uv7dzY31/YnYKXtHW1t0LxPQqLHqXrp6n4l
                        Ero6twysP1RX1r9f9cLRH47ugJ7mDYYBIovU0HRmEAEWEVTjYATARVejfFjJiktFSicDvJSC1qc0
                        jg1ff43S0lLE43EwxnDUqBFNoiDGdF2VOdholiBXGw/Me9EsHiTw+83tXwH0tCtmxI4UhfdINTfX
                        7q4uKf/0uY62wMlOex4Gji1B//79sWrdFyguLoYgJOBye37b3c5uLiwpQUeLCpvNBlUnQ5GQ6HLU
                        iMByTCVTdZ6OA0AxhacA0wwbfrMQxBDwvsAwGABzu91IJpMIh6MVHR0dGDK4AjarK044QUVpxf6U
                        Hqi2uz3bWXfzAo6TGXyrofhNG3LOgWV+3olTgG3btoWgAFht1nn0RCr8ZrNnuiBSVQD4p0H0LcN3
                        O7Qe0bmZWc7tKYUtXgyMPAeeoyvWFXjm7D6Bh0NDuq/TqyaAiyrkLnCqUUg2EOrhXPcSQjXOhUZB
                        AHM73W2hSBic62hrax5OCOophM26LiyVULx71TuVB77ee1o8LVGCxU/UyYJkKkgaBoC0lkPm/GW0
                        TO6Eziml4OlWTcOAarFYyD+dOT3y+yRLILLI4t8N//lEAsBVnyB07+t78njHoMEWvWka50KCgOii
                        KCUphbWkpAgWaoFuFELXdRw4cADlQ71gscjjq0WLuHcs5BkbjDjnHIbOIYoiGACVcR3EVAPqcaAg
                        ABUBGIBy7b3jrEWDzgcMOwc6KNWPZ1z8khPPX2LJwWv3H9ioxGNhsbu7G0ktCQdxwDDYBhAAZbId
                        uhrXdQZRzHSHADqHyhhw8+3LT3ZY4sNEyd2ma0kPZ0RrbGoYQIEYh+UvCJ70qGBpKE5AaFb1/I62
                        ti/HHDiw6eejhg5smf/ZjXfDByw++hrvvM1PBUEpmG4wQikEcx1MXA3xvE2flQwtFM+She5bU4mE
                        x+vN72hu2Dtu+/btsAgE+/btgctp6iR4ctzoDgWPF8A/gS4+OveBW18EgJl+YuU+JIkfmEx6/TOu
                        uX2k56n7toUyI4Xf32ydNWvFqH7l0j3ffLPxZCo4ccIJE0JeqqIr2Onp378/igpLGlpRf7Uh8Cqb
                        0wlBlhFPqnC6cxCMBMd9sbp61FRM/bDvue9JqShpiWeF9NQbfPrVng8mjh5xiabFjpZFB0LhbggQ
                        EEsk0dkVRkF+YYwQtYIwea2coHPmPnzDe/ARwAcaPXWi16msjXEgicmg8IGZnSgZYSd+eHSBKwDx
                        97S4+gFg+PdcsH4FmS9nXhE/cO6pzxeUj9YnXHtTcAzhfDClvB+YpQsApVSQAEgEvL+pd0aS4NLe
                        HHdOVNM1WGQRgc6WmlA04AEBpdy6jOnCOyDijuZDVbte+fN5AQAQHjcAGBAFAwosxPcSAJICIEAQ
                        zUiPYTAIgmxyawg9KqomRAjU7Irm7J+vwPqWTAv/vg+yyCKL/1f4sSII/99i4xDYAYAmcm004bwZ
                        3HARcAdAQwB6XD13bdsBgRPE43F4vV7IDB+WtEf3TGJazxMjobTH10KsmSbTTAn8iOVyxvWQMwAc
                        WHz5K9VygeVWSqUY52KQ0uSZgLQf1PVFQP/p2zorDQPqSeFwEDabDKfLivLy/vVim/gyAKBBjUOU
                        xQyJoGSHnFn2dbesHOeQkuOKCwfGdS2RR4lexBCaxXnibAKhO9p61pI7nxi8k4nBfikMaL/w58O4
                        JIV8JSUloAf7naSshgj/asy74KmIIhAZhIBQCpaWxgCAa7/+4NgR6q4FFnn/vQCozWZPHjy4r2DH
                        jh2Ix+MIh02XzXAkiMFDBqK9o6kU3GjjhvbV3EU3zbkcS2UAeNaHJNLRiFUcbDIBVQjw1H3bQpmW
                        T0UBbrnpr6cWFiTf3LBh3am6rmPIkCEwDAORaMRmFRyrigvKV0y5e0b1o3cv+uj91v2vOxwO5OYW
                        BAsKCpCTk4P9+/fjYLOjp2djx7dIMoff3zOsiX4CrB3/YmcqXvCw3e5sYoyhqKgIhBCUlpaiqakJ
                        AO3khv7qkKevOC/x8K3v9ZGflp0T18aiayc6AACrwIifpDtRVssAQPzvWJVMwaOf9Chs+jLpCAUU
                        yndEI3pzMAAA/dqXJ8+78f5Fc2+5d0f1yEMHYbQ+LRDjbEp4PkyX2FyAFRDAxhltLy7sX1dVMfhQ
                        UWFpu83i7O7u7kY8Earp6g64qSBvhF4+SzSGX7rkwXlzH37i1ucfevymda+9dV6A0p6AGwgRyf0r
                        S20KUlyZnlKVHRaxslKUebozpK+mA6HmNZ+Rrta1tIT1j/IezSKLLP4T8MNdGz1t2uaLIAbb+36c
                        g11xCgvRuM4FIoBwgBsGiJDRUv7uUCT/jna1v7v+DOXp6TsXD/vyhLn51tWLmxICkcHAISo7RU2p
                        0SVAXvDQh7PcoZajDZ6qVmGEKRHbVYdYQURLHYuGfr1p0yYk6ppRWloKp4VjyJAhoEZs5vWLlOe+
                        vfl65siZ79IRCgM0XZgIitUQ79z46krCtTJnVN2i8cQUDdb93Mh5PUUr9s2+bfymZxe9dyoV6p7T
                        YmGEQiFQnkR5/4r1tyg3TgDMpg/GAIMCFoDqzHySf2QqyrWBX59hE4IeJkSrU3RfSTQRnSJaNTjs
                        lrc+3Bq68pPXP4qA7eC/mbtj0PG2GjQZy3/R1P75Xf369WMX/mJJzpAhAQ3Il3ZHquTBrnWdBASU
                        iKLOoR99DryTa3aOccv7L6M0eJnbbQ3W19d61XjE3M9YGJs3b4bbaUEoFMLA6gEoKytDbr73KzHB
                        nrz5gbuWcR9XiQJAIeitA+A9RY49Eo5+UB/Akrcs+3lb67o3GhoaMHTgIJSVlak2KtZphl6UkgMU
                        WvOEO/zP7+Y+rmdaGO+56+lWAGJtbb1X0zRUDy4FIYQt8N8kAaZOA+fo8cP+XijA9dffdqLbbZ8u
                        CGQcIWR4PB4XrVbrR7t3N81cvvz5evDDWzL7IiMg1fMeCkD81JfurunrfZGuj0jrZaTlu30cxH+5
                        zPFS2oQN4q3XLjvG4e14qqurfZSFWDpAYAFHigpCghIOXdcFEGZQQUiBk6ai3OL2rlAANosVoXiX
                        HTAmAsIhQDjApNBGgOwJBAKfv/TSS409PyvD6FGIzCKLLLL4Z/BPpzYu2mERJ30NKkwXVAA456in
                        ne9umRUFMbBg1vl5/ife7syQFUJ6nSr/VTewh18+J/emy97tWn9/ICksEUAJyMgJzzimrp2VlKZB
                        vOFkfTjVw0MI4TmEk6QMwigo1yTnlyHvGa95En8eZhjGeEopGhsbMXJoBWKxGGw5wlmQyItgXP97
                        6xdFEdOmTSp4bdlnHToHO+N5FJzQ+dxryWRqss3h3q3x7hoCst5gzm+SKcvWIB+/a/t2QBIDP+VE
                        SIbCnVa3xw2LaIGoGm9klkuJIjOuqOJqWDEJqiiY5ev3zf9oFoTICI3ESwiIvmvv9vFFRUUoLKDn
                        XF9x94fH3M75gteAyT7AYWsqCvCtF4XCdbPsDhksacwZMiSQ+uCDiZ4zz9yVtLsSTJgsi2yVpp/6
                        i/a8WTdtKDnjKM9gi9R4ugD155wDB/bs9u7btw+nnjwB4XAYX23eCEmSwDlHfn4+cnNzUZRfuAmR
                        5D0jV8Q/4/D10WzgWL6ciL6p0E3/h/SAW7laRt0kNmkVWPTtFafr6q43Ojs74XA4UFxSAM6NQ5qh
                        QzDE5Zwlvr5DeX77fH9v2yMHcA+zfshJ7NIB5aUIBALo6upCXl6eqsxffL+yaN5tGU+QviJM3wVC
                        iAj41gNYD/j6mFRB5pyrCn7XM6+/zzWbIQ++tBolAAruYz4oAFdYen7qB1hff5JeAq2IGWIEvKQS
                        gN5+6+O3+CzGHaFQSA4Gueh2ew4ZCR0ACYMjAZAUOEtJgjXscjnDhUWFqKvfj9ZAI0C10/VkvF0k
                        4lpiyPe5UPTJhNeu3HFCO08c+VvTdR2CIGRJRBZZZPEvwT9MJEI4eNjA2vxyvrR2SWuCcWDwMc/Z
                        D2yZFQUAQ+W456l3Og1mHHbDYox9y/b67+J7Z01xAAgxSwRIccZl0Eoi64yrlMyKHjVqm/Oc8g6L
                        ZJS7AN2msyQlhEYFSPsF1fVlwl4TYNM9B3Evp+3tbShNC1JRClitMpBMvgn9O0jE4QlhGIzijWWf
                        dRjpweKY9j9eqibDk/O8Zavi8VA1wPZzRjaoKt3YWjd60x+Go9373henO2nsZAIkVC1lbWg8hAH9
                        8nHqxqGvzJ90X8Gi1bd3MK6omsaRmkSSw5Zv9BzE2NTc+a/M5CKdrGqR8VTm2Lx5MyqqS+AWLb8q
                        8by6GlPNDPJkopDx8xS7xR6tau/cPUszNOTmulUWN2KAj515JoKErKV8VUeC+4BZVz024oSB8u2U
                        GtWc0GGEwKKqKTmZTEKyiDj2+PEAJy3RaKQkFA6a548BdrsV/fqXAjpWxfd1bzrtoLtHXtsHc+Cd
                        ytMhnL7Sz3WTdPjAnC9tKXFW1P1x1aqNGDl8GMYfc2xLR0szNE33MkCf+NH0ee+Xp5eZ7mtU0svn
                        KddHgWjtpYWFJWpXV5cci3fB7XZZiaCOyzRa/pgEPVd6z3GPy6X5RfXIgdZ3xAIzCpTp6RnygJ4I
                        hg+MK72RBwDpHeC48oI3S27uvP8Ufi28c8TkZEqEIfFEalA8QZkgiI2MMXc0GsmRuBATRSkB0FZR
                        ELutFhGJVBLdkY7i7mh7Kee8VqSWHYD4kEsvXTPvoV+b7RIEwMPmcefcdMrMkBhR/K8ojcoiiyz+
                        l/A/vqOUI6UCwNolZp/3Gad/knNw44wQGLDg2lcHUWdDif++uZ8JRwjp/+iUxg/CXK8y3ZSKPvfX
                        D+exWt4JDlACubZ2pPrCC89eSZNto2EkhnPwhEBoN9Xl2uqvL/rovvuQGrYf0kl6YlwkEkFzIoh+
                        /frBZrPBY3Wtuco3/+X3jv6l9+ytfwiaG95TTmm+zRxAwYwcEAAL7nzKF4u0Kd7cvKZ4KAxChB02
                        i/hVIpGUIMRxxbLy9j8AsNxzcGwsHM3TNA1Op9NUhbSLLBQKYdHm2zsAgBJCGeeMEtDrbv9qVP6d
                        Xy0EYUUcFpeqxdHa3Ij+ZcVwE+2seWMWfrQD92DlynzLw5uQAhR+vHRokMjbH6hvrENJSQnAsclo
                        I59NKPjGtm75isQqKGz1ZOCv135wbFGR5SJK2RQQ4qpvrIVhGLBYzfNW4OqH/sVl9dFQF3RdhyzL
                        sNlssEoENTU1sEB60R2W37rp3YdagLT0s98PKDw9wBKsWQoZZk6IYdw7VvApSSjA0ZWr/7Br19a8
                        QYOrUF09qOXQoUMQiVYCoNMaEa8e/5UrOP5LQJkJq1KCZF95a6Pt+C/yy5vBud7EoVUWFBRAEuXa
                        rvau24D7RSjQf8hCGzDTDRnNhkzkTFGUHp2L3tTEt1mJDwr8ivm5ryeFwns0IXwKh0JMkSniB66d
                        /tpgydl08i03xW5MqamhEOUDFAJhBvPqnMUooR1UkGKiaG2UBAE2qw0OK0U8FkMoGilLGbHTUgke
                        hyG8b+GuJ/pHT/uck0Yy87kzGwCYVEaC+NR5X+dcs+KYTujgma6K70pjGIaR9bDIIoss/mn8MJH4
                        EcEDSpbKPt/00IQJwN3++9/tNJrOLrYXYOHCRSvuVG4//58iDkdq8Wckd9OLNHTg5JKVtrXtN3Wy
                        tN/E1Kn13qUv/vUKgbISrgdPAEjcQsRdlFn2GCrfl7cFKQCQBdDOYBdKSvshUd8IAwY4DEgG2wkw
                        nL3p5WBv1VhaqfKIzdO4otIpsC8Y+8QL3Ihc7HE64LY5mhLRhIMb8pYk7wYlrPaFNSM/+CkBzrsH
                        eZQGT43Gus02uWQUHo8Lgk5e7rtcxjkrHDrXtUApeE5T4xe3dyRRVFhSGwkHSuvr6zFy5MC4LVnw
                        yxsX3//RSwt25F5+7/Au/7Kd6uo1ADjguWfNc4fqm/Mi0W5UOyshcBosfbn40LpJK3T4gc1X141I
                        FX+6yEWEcYA92BVuc3HOUdq/CJIk6dF4QBRFEScfM/mLr77+vITr8fKmxjoEg13o168GnCWRl+vd
                        dMHdO2dXpPP7QK8ss4L007k5qqo9OQZXJwOA/CdXOjArNnnYsCE6Y2R9JBIcw0EaDKZv1xrk6+ct
                        vWlvZpn1e8FQkvbngIJK1Mm1L1fsv3uu+3qD6al+/YqfjcW7wJGUH3r6/g0PPW1+T5kJq/Lsd0hR
                        94HfT3rsxPsIZPV+ntFpUPp8B0pacxq4U+FYSMZZ/X4l2WuKAfjJNPGCSwaXVFybN/1mkhgxh/JC
                        ApZvMNZf10jEZvUcUtVUHgHCnNO4INBOu8MRyc3JBRUpDtUforF495CgwPMAuoczvpFAfl7kBV91
                        vXf1hmf3Ik5EEGAkZv4ufXkKABj0a3BMAAC4ARxJ5DPEQhCELInIIoss/iX4hyMSmUhEj+IcBzRt
                        unrcENjP+cVLlydSqbO7OsNwu91wOFxl1dXV9l27dsUlSeqxAv6X3MDSD4CiBJmzcxMcJom46/YV
                        Z40Y3jKDgxqcG2OJKG1kzBAIZwflqONjeeuF+04MIAnAOmA3XIVFJR2bNm0qIITAMAwkk0nYmLQP
                        oiBCN/TvehIFegmFpRLWW371+vkCwflUdnXmu91bQ6FgwcDSys8bGxsdPB55RQofqG/57N64RCCo
                        HO0L77N+5fV6Jzc0NMBpkRCJRFCiFT45YdMlQRjA5bJijU3NL5l9sXd7Q90he0FBAQZVVuHAgQOV
                        giBg5IiRq27feU3aDel+XH7Pvi4AOKrznJzlqxCcecf66vZwy5hIJIGSkhJEo1FYci2Dpk+armM1
                        sGjBYy9RQf55be1Ba2dXHJWVlQWqoULXdXSHu1BWVgarYHljzNFjsfnrDeWB1pbytvYm6LqOwsJC
                        aJqGwYOq4IhZ7+1LIgBA8RP4ARF+6D7O8c3cAsfRSwKxnqTEqukqAAQC58aSyT1XSxatSqAYZejk
                        FW7QHWzJ/Kf9ABTc0rPMutXQcTJ6Ch7z0EmBCviWTH8SgHjnLU92Wxy2VOiQuAfoYwC2Fz+orvhd
                        xcaZqBk5opAyA59pem5GRxSgauxU6aKfzE3ed99q+cbQl+eFbopNvvHG6vNTqXgRg7UFHAYMbgNI
                        TBDFDsMw8jRVDXvcBevsDjuYZiAWDyMei+XE49F+4CTGGd0nCq6tVMdbolFcry0/49D9gfzkVEBY
                        pqUFoRjMZto02Tc0HYKU/kmnJdQJIWIqlTosRZclEFlkkcW/Ev90slTTAEkCzrjwrWMhhH4TjyeQ
                        n18Ap8PZQSB9PHz4cJvFYlEZY9+qN/hxaY5Md4aertjMfDk9NQUVDJhyNJzjprw0zWJhJzFu1FCi
                        VXAImw2CFiIQK2G8s6CjsnHqJwhlluzsbnSDawWdnR0otdkhCAJiiTjCojoaekoHZYBfMm/Tihma
                        yAwrlvTt+4JfoshmSVzFU2QjIXCo0Shk0I7Wxvo8ARDnPPDoJjCVcwJM/PlLLuDybs5pRBSlkM1m
                        8yRiIRQUFGDAuv71kCBCg14+yz7ayEve3NjYbHe5XMjNzcWXX/0NXq8XJUUDXrVuOu2Ga9uPtT/x
                        5ZdxkJ0UAG5+8rycKbNXdMIH5DiahwhSHrZu24aigV60t3UiP7cYDxz72EzjJHbnF5+vL/J6ixAI
                        BFCQ3x/c0BAKdiEWi6GsvBAjhh3zUl6eiLXr1vykqy1e3tbehrqD+2C1WjGgvB+8Xi+KvAUPz1x0
                        3co+SgeAj8OMGfSmFcj9gdjRS8zXh3U4cAC49cXATGV0Mo4XX3hFOch9AME8cA68eF6+44qjA7H0
                        5cGgmNEDxcex0T822eOQ7YNO/LP/xH0A3MDky2+RJ00HAKjKKrDvFxw4nCBk0hmZ6zJzbSpKRn5a
                        oQCYDwoUH3qiGMkbnvzp+RMTM9SEMmjOjayCc26nhLZxSC6bLLUxAwwETkqFMIHYZpFFOOyOLlVL
                        IBrrHBKJtuYDpImD1FGIm7lB3yHcsYt3n7ThgVfGtDINnAgAngTSh1EHTNJwQekk29bAeuOgpqtE
                        BASZADDMrilKM/UR31ksnKmbyJKKLLLI4p/F/4BIHK59L5nhVDA4CedSSJIk5ObmghLymaHR2lAo
                        pOm6rh/Zfw7gf3YjO+IBMVO3OX7K8osgpC5iMCoJYOFMWiUQuU2n8Q5NsuwYt3LYXyZsGxMDgPX5
                        sA6LQhJTXfbOzk4UFxdDCieg6zqqqqrAwe2gFKASxQ9U/Q+0vH8651IDJRguCqKuGYlSADZOVOvA
                        rsJTm6+GvHPeUPHUJQdia5Zf3q0DoDxVBXCPYRhw2N1gBsHR1VOxIvfrkh13fn6FXCBcGozFql3O
                        HMiEoKWhESNqRtZSzX1P8KmC15e0Do5DWIfl80/KsZ12jnjOqe+GH5y9vP0UyILqh3HrvYVNNNGG
                        8vJyaGoUiTjD7j37qgkhT8iyjHA4jHhch8fjQUegBbF4CEUlpThh/Bm/O3nSCdj0zUasW7dhJCXO
                        ltbGXeV1dXUYVjMMiUQClFL079d/U+WX7gfQq6iNXoEEMw+QIYiZyT6e6XDIGHKlT6dP2ZzRYAB6
                        CySvWBmIYeXh1tgwyRszRaYUCp/ZHcF9ZqbBDwWcQzV9LIDn3oF1xpS/n9owZbp9PeZhff84T9c4
                        +AD4zXWdXnXQdXvk3XPuvEO7SteTEwihCAUiosuZU0e4UMeZUWBwaAANUEHeKwjUTogpQ845QyqV
                        tKRSCQfANQ5SB2Z7TEDOjrrNx25ctuboMGBqMYgS8NDLZiv1YS3UjINzBkESsaJ9XQIACCVkKiAs
                        BwzOdU4EAdOIKL6hp3QiZGTbjR5ylCl2/oeKnrPIIossvgc/nkikaxMY4RAJJZrOuSAAxwyG/eu9
                        iNusgdM4ER05OTkqOGnjnNdr+4a99dlnn4W/b5E/lkSYLZ7oNM2yMlrUpvDNvNl7Ktz9PlYE0SYY
                        nIvgQj0hUmNhYR5v72iYbPDUR7tEuvq+bRN6pJnDw76RdjmP1k5UW/qpoHC7c6CGU+CcwON0t3AG
                        uvL8EtdAIFWjqGl5YLMwrWQCXK1rEan6HVwXdy2bC6K7KUkeJ8BwFOQVtgTb64oA0j3pQOXRY16Y
                        2gR5OvrpeiozQJ55B7wn2O0hbqSgqRy6HofH48Wbua9OTPQPTieEjwcRXfF4EhaLBYCOwrzCd2nM
                        8Yj3t1eum6eb9R1YLouTj8tXN1e4tfU8XzyBQD35scv743rUP6qevOUuT12TqsVLxYgFEaojmUiC
                        Uor2QD2oyCHbNDg9BA6XAxUVFXDIpX+Y8ZvTsfTFT9DSdqiawm7dtWvb6ObmJsiyBENPQhKBmmFD
                        tspx/Had9DG9wDej158aSAcnFPNy6VMr0cfNGpybn/fUJioZFcgjIgd9vtOnGJL1fqwc7hFyJ3Bn
                        WpyCrzIJzowpSGa+20tGeJoo9F1+7xt/WhBKQa/J2A2Nbw13z2ueIsqpy4YFuoYGgglYE9b09Uth
                        s7qYoROLIMjNTqejWRQlWKwW2G021B/aW6AbegGlaAQcbxEj/wtKbFGBuwKLHj5/57cUnwkg9uHq
                        RAQOq8wRMjGW3mmc69ysFuqdvoxn0xlZZJHF/w5+kEgYGocgpW9dmg4qi2Ccc0MHKIG43Qd13pyt
                        o+w5obMJMYZyTuooLGu1BD5ct/wnwX92A594fmrhTVctbwfMm+GEAthWNyFBAPzmLOQMOPGjLzgX
                        dhskmQfCZQrL1wRCtKOjw4HEwKt3FNV9s+KaazoxGwB2CECNcebaoyMArFKic3ReXh4EQcCBQAiR
                        SAQJNVnikGx57YVH2c9d9UliMpHFVczQwU2XosB6JAb/BJ4Lj39zKhUiQyhn/QG9oKiopK21rdVp
                        4cKnhVvG/WZMy7Ik6FST7YgiDMP04/AQyATMmUylIEkS2tqaMXDgQCRI1xW1DQfOJpKEfv1K45qm
                        IRqNAloCOa78trn3zfgEiwFAx2pREiZx8HwEUqchICzfNNGOMVL8jtnP1QMA+kEg7WyXRFippgPh
                        cBz5hTZT/0FwwOG0IhjsRHd3J0YfdQzsWv5pZ038NR5Y9MpFGjGOBRcim7dsGd3Y2AQWD8Pj8UDT
                        NDgcDkgJ4dEbHrzldfgA4ieUK/h/rmGo+IGFUMC5AkXx9UlRAH4/74lyZEy8fMiYYE0TuY/rpk23
                        AkBJq1MCt3T/9vQF8/UpCyXrLDkcpu0dYVBKYbPagh6Ptx3gFsaMQoct92+qpoJzAkoFJJNJMBaz
                        IgzKAZUxYwOFc52YGvrRkifO2fet4uVvZV6MI/5nCUAWWWTx740fEZEwvnc2TYMuSRDvvmvTHJ2p
                        owRB0AVYVvOE98++RRf/lSz55zdw9lWvtN34aFfOwzcI3ZqhY207EgBwzbnIKzvx4T2ApBJijAJ4
                        B7iwCyR2FGHuF1mo8MM7Hz9rF+l5slXSO1MDADhnNfKIGrmYc45IJAJN12Gz27H/YB1qhg4vakrl
                        2mdORmQV53HAjOFTApxz2d6iEQNWT7MQ9QowuACgX0lJePv2DUNzcwvXz/XNMWWa+Ynm6o54Enxz
                        PQLDTrPut9vtLBwOU1EU0dnZCadVPlsQBBhgaGtvsVNKUVExaJU9Lt7nXTjrM/gBQicLTzwed8zm
                        ahiQhR3LQGqmwWivSzGMAURBBOUg6kyk5t9dep/ba9S2B1uuzi0sQL8BXmiaBhcoGGOoGjwAdrt8
                        9V0337l6vu++0hVfPrOQEO6lcFrbA82j6usPQZIkOFwOuFwOJBNRjDt6dG28O1APEBA/RM6h/9D5
                        O+zo/90JP/RxpliCf3sGxYxG3AWkQw8KoChQzFRHn/mVnuYRBQDxL9MzrppTJ56aN+i4x87zc/Wm
                        G6/vGt4Z7IbapiLHnW+auNlsoJQySoUuh90RdrnccDocHfsP7PcA3EaI4GYGNHCxVoRjjWQMWJef
                        nLL7i46noq+9dU0AAAgFGTnyHMeWLe9GCclEuY7cz54y3sNrg7LIIoss/k3xg0RCkMS05LUAQRJh
                        cECkhDBm3qKVO96ayZGYRKm0lzFpD5KeN7Br6hYiwHSzEv5ZzQgZD17/YTcAWMRX5Kqq6eLevYiX
                        jHnqUwJJB4gKcEoAiRAKxsXXW9dc/sJja2EKGSnT0rfqZX07NyVXS7cHTB/qzSlES3M7JMkMVasp
                        BgqLLvcrP/ZZZryuA5AIKGNg18x6ZvwxlfG1AEQKojY3N1urKgfW1x7YVV6Ul8PEmMMHAJNFIqxi
                        urGaisIkxg2gN/evroJx88JxH+Zj1U8lSZosCAJsNhu6g52IRqNI6CpGjTyqM2G4nty06arH3/kz
                        AlgMgAGcrTJAtJ5UUc00cEA3rvkZ75mWPzRgE3fnx++/8yefUoo1D85J/JGVbBqnpZJbYQsVJ+jW
                        vGhH/N13nnigrupsCDfd5CjLKcEvCYHD4/Goh+pbR2zYsAGccwiCgKrScnR2dqJfeX+44tL8OY8t
                        WWWW/ZH/y5GIH+fOtJAAd/LMawWAkk6lKOZEpc8Sfeix4770jA+Lbr3u0GCLQxsjSKnzIpHuk+sa
                        WkEphdfrRa63IAICIRlP2TnDIVGQwoTQRH5+PuLxOFrbmouYYdgoFWs5k7+msGyReMFOUR15EM+N
                        alA0s2PkBvEa6DqHJFHCOefAu1HAVJc85ZRT3J99tiqMfwnkf34RWWSRRRb/A/yoGgkqiSJPS0WL
                        dKnMOFfBgYsuqi8aPrTzdIB2cm790DBc2z964IJ1qwHtDmKAiGL65vk/x1HnTHRueXdttHrcc/Za
                        PkNHHXS//6G/inA0cNAmEFYAEAquNxCCA41/PO7hZw8iQUBQAUhQTD/uXjMkRQKgteblBLyHpLbm
                        5uYiQRAgSZJp2FVRAgZWRKzO16ZQYeW74ElwYPqvXx5cXh55sL21Q7ZYLLAIonXMqFHYt29PeU5O
                        TlwK47RbHpjx+SUzT/Ou4pPCkyEKJ3OwSYSQ5qshl/6Oq5oODgF43KjePl9b91punueYeNjudDpt
                        m+yWnDFCQMfAkgqdRMnP7n7wqrWHHYj0eDp3RT+71Hiis6hgonrdtLURQtLHOH2UA3vzE5TuEBmv
                        0RmHQYntM66duIYjY32S9rV67n7MnrGowtvPsgEg3d7cws76Q/vHtAUCKK8agH379sDqcMDlsqOl
                        pRGDy8tWSYkoBQjuz8938A7EyGRQ36rDUxvKD53Q750hU1NBek8X8EO1rj0kwpzZXLhf6V2A+W+1
                        DExSAeC265+dbHPHphPKLq2trYVddZiRASqhIL8IhAhxcNJFIIRFUUb/gf3AOEdra6snFg+PYIGE
                        LlBhJwE+FYm4gXZWvHnglUs7lul9ojNPpU+ZCGKeGQOMsZ4tVVUVsizDrB86UpkkE6LIRCKyqY0s
                        ssji3xs/TCQYh57S+oSwp2sAUFgI24wZH/xMoBaAy1tVw7YnGDjpy1WARgBghyjy7QDw48Pf34Ut
                        766NFk5Yadv1lxn8mmnveIcPa7nH4yqqj0WiBSBsoDfHmwx2d1kFw/sntI9f99SuwfFzxl3j5FsR
                        Q2ZbAACKACgGYBKLrn5I4RvSGQrFiqLRODgTEI0kEQgEkJeXV9ASacHR19009t3HsP66u1ac6Okf
                        uigRT0zsbGvHscceC+gpbN7wJcrLy9hRjWUjfvLUhbUwdLz6/IdBQkQRHFgNQOGQ+u3OF9hz5kh5
                        3BNwq36Ep148ccWowX+p6F9WPB9c3SaLCPUvLmhTY8bzt99/41pOAEpAetr/YD7FLjmvOQ4gDlAQ
                        IhLO0kNVOotiagvU6DA4dF0H1yUOAAYDRAEoyH/R3tp6RfzSCw8UDh1t+wNgaxEFAV1dwdJwOA6r
                        VUR9fROooCEv34lIJAKPxwN3gj0//UHjdQDgbjcFAsDq/xv1Ef8471yYlrb2pc3AMq2Z8+YBixdD
                        nH/r7ksk69bzKTHGB7u7irpb47Ba7Mj1FuickSATWQGlYpxS6/rqyqFBWZawa9cu6Jp63J59OwZ4
                        PJ4WQskul8v9PnSyVkzkbbj36ZMOcFajEzpN5OzS3mv8MAE1EQA/TJJaVVVYLBaiaRo3/S7+RUTh
                        +w5b1k4jiyyy+L+MHyQSmVYzwGxFY1zgR0182nnVVZbTRVEfAW7v4CAJKkT7t+/K7wCAqYCAmn+N
                        RTlVdohT152rXXDSN/ZJk3a8AdhKwxG1VoCog9Om7u7AUArrqngw5+t7nhlSB8HAe9ueju1dfk7u
                        4KnvRrAUwPR0VOLIfSNCiyRJwymlKCwshMVigdVKTNlqZ4nOGLvj0sWpaz3WzmtpUpum6wzl5WVQ
                        1SQ6m5qQ58nBsHDOhJ+8eNEhPGcOIBwMkzj4alQIZ2KKE9gPDAUALQ4AB8skHQDeem1g+2VXFT5V
                        adv92vzHH9+NaQR/HX5l2U8efOEQlt0l0qngk0y5b3DODcAAIRyEywIjqsHAoTHV9JFkZupE1wFR
                        hNkkSaggCiaZAYCH7zzV/faiD4PtgSvit81/cNKwo3Kv5rBbLVJeUtPilZFItyfXW6RHYi1iMhVD
                        zcgqRCIRRMLdGDx4MFKiXg7cDfiAuTgYmanAyjmSmXHqhyIH/yrQI2olzNRGWpGSKJg5s2TMjTd2
                        HyNLwtGc8X7z5xoDEsnYyMbmbqiqKbrl8XhAbWKUc6JT4vjY43GjsKAYnBk4cKBOIFQdRgQqg1s6
                        vG7v76nh+uTeh25Zk9kGQ9Nxz1NpzQa+3AAI2bZtpKO9vVB0u53iMcccTAIxxnkVAMQ55zAMA4wx
                        yLKMw6J0P8SbskQgiyyy+DfHDxKJTB86oUTkjOucA2edyE6yyNrZkmBPMoNJhHA3QSp3+VpE4AOW
                        +6f967ZwTQ11KH8uPPnktksotQfAiYsQoxJcaKLEsh+QtsQOXvnIfUvRwg3ghqsnuznXw3uXn29+
                        /xgwDTpHStBhUQigCN9MhH3LCKRO+Mj1NeeBU71eLyLBhnTRYy5isSTCRlxsbm4+o2r4s/s6u7rQ
                        3dYGSilyHVbEYjE4RYJBtqKLz1nSsgnJ3vg05bIwfDkIpgEf4J04MMyOgCCeki8Ln0I1ms9DXAYE
                        RmC8/MLUJsKnNl1ZUGB/oROJn+CFOvhBV3Scl8uI2alCuJTOsYsi57rOoBojlsnizotgsHRKg4pL
                        Zc5MxUhD1yGIIpY/W2WdOuNgcjUxhZPfXvRhEACuue7VoYW59ApAO7uwsKS2sz0mdgU7PbJoqzcM
                        vbS9vR2yLEMURXDOEQ6HUZFT8OT0RfOXABUyUKfCDzzLkUyXJPy/hQ9YqAA3dD961oIFtkc6AwcH
                        EljBuS2cTCZBwd2xWAJutxuiIDUJgtgki46txSVlCHQG4LC60B5oyY1FQ2UA0QBLPWUlSy2Y8hl9
                        3LVV0XvVMbkBMJZRjzRwzTlHO596F7G6pZBGTt+mAlCBqvRvykGj0RzqdOIwoyzOOSilMgDtn037
                        ZZFFFln8O4D03suOzNWa7+/AWTlOqOyhCbdo7evOTfzlCpRtL3vxTYBSDn2gimgDCBuhstZT7r77
                        gdXPPzG18Kprl7enBxgBviOU9cjh7W3TdljEN2pSOlUsJKUwTvs8gkkKxPtbUK4Xr1yg885KQrVj
                        dBb/xm71fhZPtgEcwQW+OQ8AZljfFEESDt+f9D8immkAPZ0CoH6Id8m/ezARCd7Q0dEBq2CFYRjY
                        uW0bxowZg9aGJnR3d6PMKcHj8SAF1SQQRR7079+fwZG6ft5ti54hAE7bUGX9YNzeGABwDZAkATCA
                        ySKEVTxtbvA/znUf0Qb4PUOPbujgnEOSAGWlbCnTLsi58sI3g6e13eD4pCgUA3nJYBzGI3cuf1Cl
                        XbP6l1d11NcfoocCB8qs1ty/EkkbpKmJyi8+/ALV1dU4bugx+OKLL+Cq0lBaUHS1Yr/vxR4VqYww
                        RFppiqSnm3oQh2/X8h1EnFpjprcuVyDLySrLs4sPRoDDrbV7oGQKItOOmX7zody0/wL1ASx21doR
                        cGy4iBtSi2BJnQNooyORcJEsyzoApFIp0WKxqISQJKXiZjDxwFFHjzAikShCwS50dXcBNHEcIbAz
                        w9gkULGW0+TXoVBow+9+97sGznVdgUiwFFLGFC6LLLLIIovvxg9GJJxQGQAE1p+rgQP7Slf+IpGK
                        jNN1BsYYolqbpyC35NHOzr3bCAH49uWdAID5KQ5B+MH6iI6vQVEDaArnDByz73hp4JP3Xn5AIiDg
                        MAzl7SUAJ4Rox3ImfW6zunYnU7EcDt7U1eh9rVdi+8gRNjPw6r0jVh+kFOj+RbatdntKbW5ulmuG
                        DEVRUUlTa1NTaWNjIwKtbbDZbGhoaEAgEIAr343+/fuD2ymsKWP6jf5FrwImifh43MEkB4e8TBL5
                        tDRx0oFV+rfY2b8chmGmPzJPvNOWEXHZNDUFyMGPD1RJnwx8JAYAq+4C7pr/whVuiV0sS5aOxsZG
                        tLU3l1ntuX8BSFzlOS9Fwm13FxYWori4GBs3boSmaSgfMKAp2dG1HXYcriwFIJPQ4H2m95U855xj
                        ag305TsgTq2B/pICFTjY+4TPe0+N+V+h3Kcws0XTdMycfUFt2dzSL2rmAZjDW06MS8mzdU3NJxxe
                        Koh7kkntaEpom9ViO0QFIQWOuGC1HyjpN6DRYbMD0NHQ2IjNWzZWAZA5492CIAYNXXyOcvv+L18b
                        unp94NwkY5wTQvDcc6bjl4IUx/Qf9urIIosssvhvx98hEuZAPB9r4+d9fXHO9u2IH3XUNuelP2u5
                        LZk05aQz1sRa88BHrvndnG5p8Kke1HwSXb8y33riKxYNb6T0H3oS/3S6rk6cW2xbs7gjIRGCJ++9
                        /EDJXNjybwNm3/vYHxgKxe5w03kup2e9QMQ2VQ3lcdC6Xavtjy9bMyPMkfHr+J4xW5II9D69+MQs
                        PIQAdPGRawv52r0ul2OEJAlIJGIIBNpNUSE1BSrkwOFwIZpMAokUmto6MHr0iCWnvk3fxmIwxQfh
                        Dv/B5B0ARBDo08yiVM4Bap0scLaqz0b9iwSGjhSAJIDOOcT0B3+8UNM5RACq+tr2fuKQgRAH/Q32
                        5QcOWIoGxi5mkD2axlKHmhvz3K68PQkY9mhyyNMjx1XjrT9+CIcgQ5B0RFItcLgdgJ58oOnpxzeD
                        P967UgXAd+g59JWb7p0GIC2lnYk89OV13NcnMgGFET/kay5d3t/qbRl347Wh8VRgEzTwfgC6AF5o
                        cBRRAR0AQhwGpVR8rbi4X0dRYX9YRAnhSAjd4RDaWhvBkBoFaHbG6B6BinsEVvXm4ocu/huVQHim
                        yOKxww9s7zZmuyWyyCKLLH4M/k5qw8SUz9fkvXv8KZH8AgizZj1zY9O+PYsYY6io7I/29nYUleU9
                        ede0eTfuqCFYDnNcWf74RPvUaz4Lg3+HjgQ5fEAlZLLM+Cr15GnL3WuXH5PUeIUKAPcsfOrPzNAL
                        RCZMJLB+kpebv7Mr2AWOaMGXb1139XvbEOMMvHdg/fZAzbnpQwBd50QUCdN0DlGAYQBMACwKyF3i
                        8y8RHv+1nkxBlqz1tfsPlHd1dSERCSMcDsOIJlBZWQmdmMTphGPH/WnOwlum9QTcOUm7TZpkhXMB
                        6SYC6CogWr6vve/H4sj9Onx5PD2dEiLojBs03e1RdeoS+9qP5+oz74CrxvrBCXb94G/BaZuNEFbf
                        eHCizeWGwHKfDFPPtquv/eW6l5/57QsbN64/9thRNWhsbITa1YXhw4eDd+0acM+QFU2HEwYzFqH4
                        eFpMusd62/TC4LwnRZERgMqkKMxtNq+TDSO3eYadvOFoA80/5cQYSCihAMslgBUQkwSgHFoOQFKZ
                        HSdE+mZg1aBUKpVEfn4hnA47mppbsP/AbiehrIwSFHOOBIH8OWfyfq4VfvjA45ft48z0rwDM4lRC
                        TU8LyVIpp1L7VUmiPYTUrGMw5VyzdQxZZJFFFn8fP0gkCAQZAPhSYP7uRana3bUoLy+HbsSQn1u4
                        ZtMf/3jWsl/9XlWUEZgKUzdS2QEow7luykNnlnTkQGi+77lLp18YBrBw4eOPcYIKw0hMsVJbvSTa
                        P9f0FDgX993ln3HXuOqgfcMBb3zgwIH2Awf2xg/f4iOIRDo6TaiFMD3FIcg4zAeJA3cvfHYhS3Uv
                        EEW5Xk3EyxsaGmC3WlBbW4tYOIWuri6UlRZBlmXY7CKOG3v8m0Z39723Pe7/pudJVjOJhGEwiLIE
                        EEDXOUTxHy27P+I8pD1OQDKNJ7JgbrZ62Iz02ZcsaB6sq/5JhgwIeBaibyaQvGvfWRLWvCnTFMBp
                        bTIeqIxGo7Da3DuM0LA5D75wwZ7de2C9S7lmtyAIGD+qGh988AGoHsFPTz31w5v8C8/KpCDMS8WM
                        APU+uZOerokMFqa9uzKmoOn6BlxzXnOpa8C64TrvGMhJ9GhCtaMASg2mDQInEUGQazmgAmBgtAsA
                        IwKLDh5Ubbg9LrS2tiEY7EI4HLaKIhtOCLcwlpY+4zgIw/V6LDZgfdf+6lDGAItQiJyZNRqcAVQ0
                        oxGqZkCWv03qeh1psxLVWWSRRRY/Bj9GkIqhDlTZ+emEXbv3obykCG63HdF4AuUNAy6dd2CjuiNN
                        It7dWCUv33ZQV6an1B0EYo0m6N8ruJMeezQNsFggM2aO+L4735gqSrYYIakxNqt3c2GuEy2tLaMI
                        yL4777rmLs6ADfu9cQDYu/dIEoHDBrhMZAAAONM4CAFHutgyrXykKeBbQ2MeHSZv2GSoxGKzGtcO
                        HjLweIHJH+bl5Z25d28jJKsDnAJEtOK4Y49DIBC4wOmQLnhy/mO3VbQ+8Kezn7mllhAAIiFiWvGT
                        ECJ+n4XzvwL1kGUAKIeqjlgmi5hprmvoYjgwD0nMgB67e/1ED6l/dveeWhx3fA3q6uoqg4FGFOb1
                        /6OWcnz+4AsX7AGAJ5/83ZVG0kD/8v7o6uoyCZPNhlI58rjvyLoIJX3yFPOt6egJZESf5s0zp18V
                        e36EbW73QNvMUxtukbaMvQnBkUQwynWwIoDLAO0A7Ac4dDuFWO/yuFWP2wVCBMgWCYZhoLOzC+Fo
                        e/mevTs8hHAHB6tMJpN2m9W+A1x6lxm0jcGyhTNn28FdtGnFR1d1A8C46ufswNGZtli9oOA8W0fH
                        igQVCdF1nYMIuPDCa/PeeefpTsM4/PrMOmJmkUUWWfxjID8UuS2YC9uIWTCOe+qB1I4dOzAsndIY
                        NGzAF/mHhKkzXnqoEwDqEDD+Fhop/zJnW5JzXQcRCHRwCN/9ZNc3EsEYMOloOE+Z8tI0IiauBoxB
                        ZWXlDeFQGOFI41DO5AcU/5y7DAMQJSIyxnTyHQrN5r5kUiaZxLsKnGKRsUpXAVMvUCACDACSskPU
                        lBodAIT0dlx3dMj59DZPjAN44M7H50WJdWZd3f7yg3t2oaSkBBaBoaysDHoyBF3XUVZSvKYgLimX
                        LrllNQBk1DMKJlxpb1//QvwflwH4wYiEDABBjJUAYAI2pmwcwkZiEon8JbAE5iJ1w/y6MTm2VYsC
                        TYHT8vLy0Nq6Ew6HA4QkXtQCtqce+6M/AACNTRBvumn2/vbmLkyaNAmRrv1oamrChAmDcd1P38xV
                        Rm0NKWmbbsVn8ge/H/D5AL9/tQw+SQUBxv8s4h1b+f4ggTePIzx2HKW8HOCiZLjaOZgdjIZzvbmq
                        IFPE4wkQQmCRZPTrX4xwOIS62joQqg8DMZwA0QFuYYbhoCL2EsDKgTBnJEApD+i69b2HH5333mFH
                        LV10ymD01O6YkRQDhIhE11O8rwNmZaUo19bq6vdFHHRdhyRJ/1fJYBZZZJHFfwJ+kEioOnDPwvcX
                        7tz69oJoNIqiHBlVVVWw2tify958c/olW7ep27aNlF0jt6kAUEmg6XqKCxntf+G72xd7EioaIFkg
                        +u54bSaEzpsByCXFJR2RaBTRaMRDSXTrX58c/os9eAGtrSsS6TZPmXP9sIp6zjkYM6soMwOJ+YGK
                        dbPHOSc89U0UAAgRxXQXqGnElf4vAaBkh8hZjQ4GrC+G7cQWJOb9dusUi77hyY3r15S1tbWhKMcJ
                        h8OBkvxcJJNJHGo4iNNOOa0+t1u+cPqDN2wkMiFc57xn/P9xlhF98I8RiVxsTAIAJnHgZAAKcKWC
                        AaXCcl800vDrAkcRWlpaINnCEKjlm1iseeETzz75MYBcALht9nvn1DZ/8IRNdmHQoEH46sv3YbFY
                        MOv8vJNOHfXlTuXYL4MK0uLiR+zGBRe8X1ZcES2jvLkG0AcCEKFTEMpyAOahBFTitB8gNBcWliTd
                        LhcSyRiC3d2IxkK5hLBKw0iWM8ZkTdNgsViiAhV2cphpDTDEwW2vivqg7TvWn9OyYgOigtRb42AY
                        gJCOqem63tO5kjHDIkQkAATOdZ0QUayoAK2rg1ZRAam2VlcNwwClZuGEee1k7bazyCKLLP5RkD6p
                        gB5fjEyemBBCUhrni+5dGvt6/cd2j8cDogdx1FFHQZTjz8+55OXZqKgDAEwD2HIAGtd1xjhkiAA1
                        ix0Z1zl6igLrZJ1VqBQApSA3/ebzalfBV4+KVDjZ4XAfpIQjEgmVctB6gTvfuePu6Xcdvsk/1FF5
                        5EDw3fPzI+Yn3zMDIZCvfxJDPe3P3rNjx8Ypdbt3QpZlDCgpQmlpKWSJQFVVpLQUysvLo3osdcH8
                        JcpHmfGfab2DHQAYOofQt26CmfKVhBLCdcaJSM0iP8a5rmogVIRASR9CJqYZ2ljpG26Xrv64Sdt4
                        +sFkuiAEM31adZHwwrvJhFqt6zq8sh1tbW2wutT3dU38wn/ftQvdrgI7gAIAmD3z7rq9e/fihPGT
                        EIvFsOHrDzHp5FP/plzzxKlYWgf4uXrJqbUFABBur1SrJnw8RBBbyzhPOYF4niFYKEF0FCXMxbjQ
                        bh5JlmOxWFBUWITWhloAWqVhaANAYAHnEQCcCtwDAhc4+QqAyoEEZyQiCKSDG8JecM92Gf0b73vs
                        /J1//wr+vvP8j14nWQKRRRZZZPE/AWGMgRCSfoozb6Z9X8+749DZzY2/ezcWbEEsFsMJ4weBUopj
                        XAf6nVH8Smdger5QH3STcc0H1f/T3p3HSVWdeQP/nXO32qu7qvcFuhFRgi0iauIoUWPMRCNg3gxk
                        NDiaOK++jHEXddRAgaKRmGiiMS4RcXk1aUzimmjiBB1hREVAutlpmm7ovaqX6upa7j33nPnjVvWG
                        iBtC4Hw/n/p0NX1ru5dP3eee85znyUyymJZNu8hdiQtig0NgyrTHfB++PTcBAVAKddY0eMZOXTHB
                        H9xxCxfm0RRQPW4PT6YSAEiaM+PhyKL/+/je3+9fbiDx9dcQOPdcoPn6WHFRoPaZrR++d8ru3buh
                        E+HkEZSWYcyYMejr70U0GoURcGNM5ZgdmaR65aKfzvsr4DRvmoqX9TVsembwXXFA1SNEsIgAHapI
                        OeLgUGd0I/c7Yzaeur/E86ObW5O5pMvcGz2LOOWwF87/TaNppSszaQ7DMNDb2oLx1RMaW+LbkOou
                        nvPgsmvXAzC+fe7SYFVV8zd2bNjyOKUUZ077FhoaGqAZGYzNO/kKW1nTbgleluYnxrhI5QlYXgGm
                        2rCCIGYloawSEPkMhc+Cp8sItf0AKCW8iBB7omWlxyYSCTWc505DoJ8Q2iVAOgEkhEAa4CmFKik7
                        NfZeK+1Nrf9gQu/Kupo0AJtZEJ94YEAGEpIkSQcVYYyNGM7NlVzIrTaILHj5sbq6v/+7gjhcLhcm
                        TShhKshLN5zTPwcvLcWyuVEbAH64DFYmwoWW/WYnwunNYavOc+degRKo06bBM+205WdreusSBaq7
                        rLwipig2mpqbdCHQxlMVkUVLZv03oVCFsD/hHPW+TgQfv3xyf89DAPX8pxD808WIAcCdt//28p74
                        zkfWvfuukyNRVg5KKcYdPQ6WZaGlpQWKoqB0TAV8nsBLzNSeT3aFV25ZenErDUeVDbEEa7SqzMGZ
                        HhsYXrLAZgyqrhEAVHBhD7bFGpUDKLiFGTN1o7PzLuPdd/9z4Karaid6w83PUbgqd+9pDlaNPRp1
                        dXWYUFmyGwC3O/Nm3fnMf3QCSD+8scv824KXi/Lz12zp3JXESSedhPaWbmzduhWnfO045hbFixJo
                        3kMo8hj31QBCB+VFgDAA2gsIlUCEAFBOWJgx2wCEGwBXiJaiVI1zCoMxM8+lmw1CkK3cUlcLeLeq
                        8PWmMnp89RuVbWt2fDM5Yk1odofnpqmEEFCVUfnA+5wi+qSBhAwgJEmSvkgfGUgQQnIlp9UF85/a
                        tnnzhmpuxVBdXY08r42qKJtwEX+uBY/uTCFbP4FhZMCQCyTG/3y9Z+vNU5PcBMrL4Y5GYc//yRPz
                        FJK6GQRaYUE4Fu3q0jmsLnDfsraWvN898sSsNmaBKQpGdVP8OJ/0hPDpAolcFMMFUH4L3NElsK6b
                        s6cwNO4Pd21v2HTpnqZmqKoKt+FBVVWVU6DLme/Hli1bMPaocXC5XMjPzwcR+iuWqdQKW+kQ3M50
                        7yjfnOoOmSWTt1Usvu+yekKzjbd0wGbOlMgPjof/qTXoV/a1voYB9875aymbuP1RTszzkgMm9Xg8
                        WLfuQ0yePJnzVD902/d0nGU2Usu7M+5vP0+h7J8J06J1desnV5dMRH5+Ptqb29HU1IRpZ53W4EHx
                        0pi9YxLh+jsc7ioB20cUexyBXQoICkI0mzEfiAinzYyu63qSUm274NoHXHjfUTD5/V7j9JhH2ejv
                        3vp61+9eDMVzvUByBtua546KbQ+OhI3IUxidwvOpAwl9P9vJQEKSJOnzGJzaGF4rIPcFP3fuzUd7
                        XYXbWlpaoKAfoVAIlUXut+bNn39m4vKzAr7H3ogLqoEQMnjC1SJQMxEwPVu/gUGHdhZ0vAnr6oWv
                        1OTbbZcAqW9QqOUg8BJwCEF3Cth/zvRUPHXXL7+7EQCWLST6pZGMufeJ4IsyuvqxMuqnQ8BJwsxY
                        kxglznmMUuD74+E59ntLT9c9vX957733qLBtEELg9/gxduxYpAYG0NvbC6KpUFUV6YyzUqG0tBh+
                        v9+0bVtPp9OIxbrg9XoRDoUeJ4yshcpPBlAgiCgB4LOFewUETSpE9QM8RAEXwAnAbYAmKHTdJukZ
                        H65brxNCMLZiLNra2uD1eqGqKsryyv9cHKhcvTPWDgIt3qsmKjVNw8b6VTfouo4CVz56enqQ7OuF
                        3+/HxJpJb6q2690+JX4qY2Szl7iCoOwoJqwSQlEIgEPQDYKTRkAYAvoKCKUfRN0Zbx+/7eFnp3dk
                        25nj61PqfG9vqEkAw5Nhh5ZYDp9Cc7qbDkUJtm1j/HhDb2z8rL0u9lUHQtaHkCRJ+iKpuS9vznON
                        r5w/MCbg9xsVtmUhXBBEOmHB7XbDEOIlAPA99kYcACglJFdhkjtXj/bolfjjJkD97umvTfWS5isI
                        lBqvN8QHBvr7UslEocflW6Xw8HOt9VN//+s/HBUFGAjVyNq101x1dSfpNTUbEp/rE37qVRMjEVsA
                        OA46EQB1SlIDwLMNSGpn/ejNmcHW4hNOIsu2bqj7jtMqmmLHjl2oLq9ERckYpIWNlpYW9PR2w+Px
                        oINEEe3q0SmlcLlc8HqDiEajyGSsywghl3V3R+H3+5Gfn++8fcKOdbvdsFgGqVQK3GLIZDKwTBuq
                        qqKlqRmcc5QWl6CzsxPr1nwAr9eLRLwHFRUVMGDQ/p6+YqIYGiCoRo0pib6uqRRuxHuT0L3dALGh
                        GwKlZSHYSJ9iK+nTAXQpipgskDQBYoJiLYW2ltiuBiV9/MrFvzmnUdgAGbaKYsR+U4C3N9QkGBNQ
                        lJHlp4GRHTH3dYB27cLea3w/sX00OZOlryVJkr5Qg9/ke9Xh4QQa9Xp8QZ/Z2dmpB4NeBIPepMvq
                        2Q3YzrJELmBlsl/VAjAoiCWc35ftMvTuP88M7GiaF55TXjeXEFLKbRHkhOf3J/upAsrd7kA9zOKf
                        DXSct/bXf9KjAACikaHzTd7nqA7kXHmK7Ilj72WYH58zMbi5SongGYZsPQIseMJZLzjrUp5ZARMo
                        i5YvuW7Wf1StOUYPrr0B5sCc5uZmtLa0o6ysDH2pOAzDQGlJOdLpNDo7ozAMA+FwPhhj6E84uSem
                        mQbnHFwwdPdEkTFT4JwjmSIIBoPQNRcURYFbd0OhQMpMwEynEevqgcvlwuZNTp0ITSGwMikUhPNQ
                        GipoBES1rWYqQcTRgNB3bG8AYwxtbV0oKSlBIt6Onp4eHH/cUSDURDLV5/F5fe8oxLWJMfW9El61
                        SrXc5sS/XtDyza0YymmgwEKVqG9VOdkbKy6BhUhGLNtl6KGWAuWfTgOaegLkhMC2pNO3wgkcnGCL
                        D45EDM+HGDwy2ekNp4bDFzyC8DkDS0mSJGmkwUBiqKWzc3/wClOQ1Pbt2/XTvnochBAeFSIJzSDI
                        loTO9S8Yree/z/czq6qi0PP+k4KjD0T1U2ofJ7iiDgwMIOAJdJnxf569+Gfj1gy+B7pRFfXO/SZf
                        ibuoKMXc7oO9ixyziarOAvBsxJnrL30JbkwCK18Cd8tNSGnmSesJPeni1n/BdS8c/7vrOTInGcS3
                        KYaOf+2KdhanUin4/X74A17Ytg3LMtHZ2QlCgaKiIgwMZJxVIGVjnaZhnCOZTEJRBGzbxoA5AM45
                        0qqFVCqFnu4oLMuCx+MBYwzhcBj9/f1weVUUFhbC7/NBEBHujkcDuq5jY0MzysvL0draClVVUVpa
                        6kxpUYqSkhIUFBSgvLDkj2kr+arSk1r1zYd+0jB5NlC/HJjEMDLh1QZgM0S4YMPbwm+MGGpqQo0+
                        46K6AQAa8qEowwKAXBXJXBCRq/0wunbD8GqTsqyDJEnSoY1wzmHb9uAw87AaEvq8efNOy3NPfPr9
                        NavLi0vyUFlRvdpivQ9E7rrludkgSq1gLFchUEABJbNVzmvZFVc8W11ZmpwHpL6tozcv3h/P37Zt
                        G9xuN479ysR6VZDlLJ1aees9d/996J04Pz5xbt0/CBMCt9698FRvxrgQ4OdCKPkAkgrRw4lEv2fP
                        nhZYlgVhO1fpuT4WmkKh6zrimRg8Hg/sjAvJZBIuGkAqlUKiNwZKKZjohW3bcHuc7UvKKxAOhxnn
                        uqpQtTnBxRjDcMUgBG9tbSns744jk8lg6vEnIJFIoKenAydOPPVWqyv5vLHd3XXNGz/sJUQlQkCL
                        okApQFfqYO9DSZIk6dBFhBCDwYMQApZlQXeKJ2L69OnhU044P9ra2gqPR0c4VLQNMN+/ddGVF2MB
                        QBbOVoT4Cm68sank3p89sQcA7ljwyzd2Nm47W0DBwMAAkr3N8Pl8KC8vR0lh0SrFtO++YdH8V6GN
                        utQ8TAOJ3KU8EU4ugUKdQly3Xv1ajeZrPQFK/78CIqASEgREEERUdnd3Y6A/DsYYyscVdxECv8ID
                        GRCi6CLQt279mvJYRyuEEBC0H0IIhMJeeDweuH1+hMNhphD/Wk24n+/TQ52Cq/2AqYO1PJeKJ6Hr
                        OopCeWYsFtVLCkpfHdN2/o8v/G11EzIQ0O3ssJSmy0BCkiRJ2p8RgQQwssIlANy5YGnzrqaGysLC
                        fHi9Xp5Md9NUz9fHJ9+Y1vbIxTCv/vtfPIHjdpW6CxNzmTCvWf3OWvT09GDMmGoYhoHjJpTGCEQj
                        BWnRmHjtpEc/ePK09hdTS0pOd9/UuXLoJHWYBhICAIeAsAkIdfpBODs++3c7m+g6LBvk2m8P5OVV
                        /1eeRVPKgke+3zDnzgeLa//zxx1CAPff3HTq7szT/5NK9MHr9cLt4R0QiGmq+ZbLtlYY3d2rrznZ
                        ak8ufAaetqFdesu1fzg9g7oVKlVQUV4R7+uOuShRYsFY9RlXPXDB9lxXdKg2ZhNVrRWwn/1Njfei
                        uZ8z2VWSJEk6rKkARkxtcM7F8NoSBP6/lpWVXdbR0YJQKISeXoZQyfr/KvzBpqcXC1YZPL33klgs
                        hqZ3m5BOpxHvS8Dn82HcuHEIBAIcvDc/3p8I5/uDTdc8Gn8S7S+mAGBEEHGYoyB75QqybOsMVcvm
                        AQxbn3D/695e0Bm9gDOisaX1jIHc3/rIO6dmMhkUFBSaAV+ws29gT0CH8dR815YHEKnNNeSA5/8N
                        Pd+lP4qPKStsuTvdy5Cf5zbdHjc625Pw6qFfXfXgBdsBYJka0S9lkWFLLZl4bIlqXjT3YO89SZIk
                        6VA2eB08sv02ySYEWjDN4BuKptaXlpcimU7SYLBgm8WssZ09bbfXb66/ZMOHG7GrcTfSiQx0asDj
                        csHKZNCwbSvq1q+jbZ0dNOD13fm9hb+9CFF3OqISMviq9t4rJgj+8UchRrJhCxOUEGLZDBYToGS2
                        SuhQomq23TX21T9t7UM1CWY7O8ZSeo735wUSDHZ/d6IbTOB5K87/iMj/t4A5Qw9KQyA7GlESeuHy
                        3r7o14LBfLOoID/d1roHqqFs7G/OexIUiKiEnIyFHNQ5HrXcYgCwopF9xhoOkiRJ0pGCAk4WvW3b
                        g8GEoihQVRW6ruPV2m+9tHbVFdMAfZVtKwvlggAACulJREFU2yguqHpdVV3rw6GCmG3bMDMclOhw
                        u90ghCAvLw/V1dWYcuLxfHLNlAdmLd/tqXjhvXuq0GgBEVhnzwhGKNGzL3SwP/+XxuJMaIoKTSXg
                        opYpKgajJqIM3UBzR4VBcBPabGfU6KJvInDzTb8/07Z7zyew+wHRQKG/QG26fMZj4xuBpQCeyL2a
                        wO8BAOT1G+AF6bvGNE2E8kNp3dCRSPQHbGbed/dTc9oAE5FZUCZx5qRzCAFCNQKhDHUelSRJkqR9
                        UIGh4kAjKw06VjcgCSC5IKI+o6l8Slu0BQTaKoLMnIqKCjSkdmBgYADhkA9CCEw8ZjzCpHzG1bdf
                        9jIAIHIVTgOQDR744r+JXgD40RmzA4+98Wxc0dRP837/4RAAKtm7ZoUQApRqpKoK2q5dsDi3hJOn
                        MrQNUQC8DwoBBL/aGLKVrq+CiTYIWi9Am7avnn7XCysnJHGf85QjXvgS4L0nnxFvE/pgW0uby+vT
                        EPD70bx7s8vr9bT0dvztZWfppqGjFgBUsmsZtJt+CM5ZhtmWAKUE5PA+PJIkSdLnpBJCVKfwD0YE
                        EYMjFKqKY+551LNz4eWP3Lho2UY1FdYAwOeOaXlBtfKUk8MnAnARZChAEr6UcsnVSy5744G+m4++
                        6sF7tufaZEe4MIdPZSx9qzZ+sD/8l2F0V1VVNYgQTDirZJjAsFrdtm3jjqcNHZfCiiAjACCzA+b1
                        t62bFFBWnUfRP0eAb+FCeVtgQLywckISADIWmG1b8Lg0FQAHNAoAv6ur/3G6zz/H6/XC7Sbw+fzY
                        vbtZHz9u0vL7H32tF0Kgrr5Gr6mpMwGgJVSg1Ip2E4DTuvxz1JWUJEmSjgxE7GtiPrdBtlSVlZ3B
                        V7Kbk+xJJqI6oxqRTHalo0EIFgC4FE4GwNj99Eo4vBIiPsa+u1NGYJAIMmLJS+Xum2a0pAAgstFQ
                        I5My7Ic36hUh9cmzKOuYCrAJXPBXrKT37RXu/sa6X8xPJgF4nKcRGLY3515x6zG+INbwdMhDCMGU
                        KV9JNDQ06Bx9ye63Thzr/97P6eJr/twLGLlmJtlETTFYqRTAEXR8JEmSpM/iUwxcZ098uWH6bMLk
                        gsywqoefo6C1BOSCCADAJGeHV5LXTmQsdaoFEufC9Uo6FV310MO/3CpEF3sVwHeGHk4A4MJF0EnL
                        0nB5QNnQHetWfYqBicdOTMS6OyBgcZH0n/erFefGoZyNxb+YXXDb9TgiRoYkSZKkA2O/gUQjnJGF
                        O6FaABABy16rZpeHDpbIzgYagwMc43LPfWRn/g/uj2wANqotuhAUP+EZMZTX6Ny5XTBReMEZ7ssn
                        zK3iSNcAVjugtSZSLdFXbryMAU4QQdKAcAHnPw3qHQD5/XzY19/Y9pjTaJOisDiYDhX6UL+xQTdU
                        z8u3Lbn6HdDsKMj1mWgEBgGAyMHeT5IkSdI/pM+eSpcb8haf+RkkOImulFJwKKAgsG2AC2eW4rsV
                        M0t0MfAtTtSxliCCEASvPPOcjlMumUPJLYQP/NSEcGm4eDnUVy52Roaun/fgbQBqBKdqQbjEDPv9
                        2xu3banWVLrL21V5MyhAKEhNTY03skFPLBgMDCVJkiTp09tvIFGFjAl81BVrbqpj9L9nciMWSQgx
                        2HY754ifcs8NPQxbyUEIgbCHilZpKgEE4Hf5ym2kvwYBKBAduhDtp1ziPHrgpw8rnuxBeHqWE0TM
                        +beu8vIidraAVuTx+ZJC0AaqmOBI66qt3HftQ/+nwXmF2cqHGzYkCmee7u580SkMJlMiJEmSpM/i
                        E2Q16KNu+yH07E3B5xnwOFI4cxoKeC78ohg8q1PKj0ol4mHCWViB2uiyvFt+G1msA5bw4Ao7O+uE
                        ayJw3XQjxhYXPfsEIMYLQSmBioJQWawj2nKMwe1F8VdfecYZjZitVqGLAkD7C0dOdVFJkiTpwNh/
                        IGFnb7k6ifssVKRgrzrQH0GMuh32OD52GSXP7lsK52duhezk6fARNX2GZVlOtVFB20M94xv/PVLL
                        gS0qAHr/r8YYrwKIdb8YVvDg31WIaZ3tXeVmiqkQ+sZYrAeGga4z+bald697MwFuY0ZBtdbIV5iU
                        QBV86PDuhdh75XNIkiRJ0mhyncUhRtOgcg6cefymMULYJ4ZDRXtU4qrjRHi783dU57ZLguDaq5ut
                        7wAo0uufE4QXJJMDutvtRjhU9n5l+dGbFOJqOifTdPKJfR/2A8BTF1wQfrHznhQAcAF2BBUWlSRJ
                        kg6Q/c89KJ/yDzInYqR97j/nal9VVcKREYrqbGgJhamzod5y9PqZzXtaJ9OSvC4CYzWgmvba73wI
                        lNsAhAcCz9wA70Zy38OEsdOoayDhcjN4Qr6dRx9b1L1p8wclPF360xN//nRb7hX/7eWXY7n7ueOy
                        7/8AMsqQJEmS9k+OSBxkN71U4KIwiC1s2MKGRpxzPOdqTyAQAAHtA5Q2aufXnz2znL95PQwA5PXr
                        4d5EHlooSPo8AIjH+3wejzdNCF29afMGRYCvXHzvRW8e7M8nSZIkHd72W9lS+oKNWh4hsmU2OKiz
                        /DM7EnDzTa99WyH1j1GbrRQApTAa7r73ursAYMHct6st74dzOQb+xeZmocvlNikVPVzQ9YSyGBG0
                        d+Ed864c/jqSJEmSdCDIEYlDBB11xueiJCo43aKqHlDoKuDJAMCjtyDMvFt/IJA8N2OmCwkhKCwo
                        +R8u6GZKjM2CKzsX3jHvSmY59SIO9ueSJEmSDm9yfeYBl1v58NE5B9/faKgAsPw42ExYQjsLurUC
                        ptAZ4yY6GbMLCPgYAnv3/Ote+SdL2X5X2kxM1TQFlmUhEPC1RLs7NQrtL4AduOPOG39C6EaV80lM
                        iCNjYYwkSZJ08MgRiYNs+STYyyc5QQQFAQqdxaJJa2wfVfTdBEqgrGxML8BOt5Rtz9jCrNE03TRN
                        EwUFhUkuaD0R7te4rTUuWnzNbcwCmDWJcdm5U5IkSfoSyEDiIOPICI6MoOAAbGRqnSqVf9pa2M6h
                        bCNwNW/cVPe1jq49UwkhSKUSOmDTYNC/iwt7HQV9f+G5c3++4vHj/wTAVnVAUYELLzzKs48KEZIk
                        SZL0hZGBxCHgG8sMPTfFYUSc8qEtf0QKxNXDeN9sn88Hr9cLAR7Mzw+ZHrevXYDuFiz4eLpn3DNX
                        3tvgXdl5WkpwDObO1tZuS86cWeI+2J9NkiRJOrzJVRsHXK75aba8+Ees2qDZDpwcGWFnt9POgj6t
                        JuE6J/CHXydSnbPj/b16cXGJCWG3C9AOCO35/ubpT//iqYq24c835ONzMyRJkiTpiyCTLQ8BztSG
                        MTIUKARvyfjYnucuuaLswscTbrc2U3CxWQjjVTtV8eZd90+vF/wIb9EuSZIkHXRyROKA2/+IBAAs
                        hKECsG+Hc0C4AIzvQxXPgREVRDDnkUQFAaAxC6bggLrPPmpyREKSJEk68OSIxKFFAZxkS0oAqxbM
                        zsYhAk5wwSwIRYE5NJWxr4BBBhCSJEnSgSeTLQ+WvdufZhtxjuzHqeiAEBBEcVZjCDAwm4EQos6c
                        Wej+mP6dkiRJknTAyRGJQ97IIEFVnQIRQsAGovuOIEZNoUiSJEnSgSADiUPOyKmK3zw7xacUDfDK
                        cLl68pTNVgGi9sq6Gr2fTaPnTnk7DRiWsz1zQgchpzQkSZKkL49Mtjzg9pFsmUUIIRwZsTC7auN2
                        ZJxky+z2OkhuTEGLokAp2HsU4uMDCTkiIUmSJB1A/wvVpCZA9uEVewAAACV0RVh0ZGF0ZTpjcmVh
                        dGUAMjAxOS0wOS0wNFQxODoyMjoyNSswMzowMEddEIYAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTkt
                        MDktMDRUMTg6MjI6MjUrMDM6MDA2AKg6AAAAAElFTkSuQmCC" />
                        </svg>
        
                        <div class="bordered" style="width: 60%">
                            <p>Para pagamento via transferéncia bancária:</p>
                            <br>
                            <p>BANCO: Millenium BCP</p>
                            <p>IBAN: PT50 0033 0000 45495615315 05 * BIC SWIFT: BCOMPTPL</p>
                        </div>
        
                    </div>
                    <div class="column-30">
                        <table style="width: 100%">
                            <tr>
                                <td valign="left">
                                    Mercadoria/Serviços
                                </td>
                                <td valign="right">
                                    '.$sum.',00
                                </td>
                            </tr>
                            <tr>
                                <td valign="left">
                                    Descontos Comerciais
                                </td>
                                <td valign="right">
                                    0,00
                                </td>
                            </tr>
                            <tr>
                                <td valign="left">
                                    Desconto Financeiro
                                </td>
                                <td valign="right">
                                    0,00
                                </td>
                            </tr>
                            <tr>
                                <td valign="left">
                                    Portes
                                </td>
                                <td valign="right">
                                    0,00
                                </td>
                            </tr>
                            <tr>
                                <td valign="left">
                                    Outros Serviços
                                </td>
                                <td valign="right">
                                    0,00
                                </td>
                            </tr>
                            <tr>
                                <td valign="left">
                                    Adiantamentos
                                </td>
                                <td valign="right">
                                    0,00
                                </td>
                            </tr>
                            <tr>
                                <td valign="left">
                                    IEC
                                </td>
                                <td valign="right">
                                    0,00
                                </td>
                            </tr>
                            <tr>
                                <td valign="left">
                                    IVA
                                </td>
                                <td valign="right">
                                    0,00
                                </td>
                            </tr>
                            <tr>
                                <td valign="left">
                                    Acerto
                                </td>
                                <td valign="right">
                                    0,00
                                </td>
                            </tr>
                        </table>
                        <br>
                        <br>
                        <br>
                        <br>
                        <hr style="border-width: 1px;">
                        <table style="width: 100%">
                            <tr>
                                <td valign="left">
                                    <h2>Total(EUR)</h2>
                                </td>
                                <td valign="right">
                                    <h2>'.$sum.',00</h2>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>        
        
            </div>
        
        </body>
        </html>      
        ';

        return $html;
    }
}
