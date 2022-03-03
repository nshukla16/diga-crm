<?php

namespace App\Http\Helpers;

use Exception;
use Carbon\Carbon;
use App\GlobalSettings;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class TranslationPerformer
{
    public function translate_template()
    {
        $arr_ru = include_once(app_path() . '/../packages/Rkesa/Service/resources/lang/ru/service.php');
        $arr_es = array();
        
        foreach($arr_ru as $key=>$ru_val) 
        {
            if (is_array($ru_val)){
                $res_arr = array();
                foreach($ru_val as $item){
                    $trans = $this->get_translation($item);
                    array_push($res_arr, $trans);
                }
                $arr_es[$key] = $res_arr;

                Log::info("'" . $key . "' => '" . json_encode($arr_es[$key]) . "',");
            }
            else{
                if (strlen($ru_val) === 0){
                    $arr_es[$key] = $ru_val;
                }
                else
                if (strpos($ru_val, '|') !== false){
                    $splitted = explode('|', $ru_val);
                    $res_str = '';
                    foreach($splitted as $spl){
                        $trans = $this->get_translation($spl);
                        if (strlen($res_str) > 1){
                            $res_str = $res_str . '|' .  $trans;
                        }
                        else{
                            $res_str = $res_str . $trans;
                        }                        
                    }
                    $arr_es[$key] = $res_str;
                }
                else{
                    $arr_es[$key] = $this->get_translation($ru_val);
                }

                Log::info("'" . $key . "' => '" .  $arr_es[$key] . "',");
            }            
        } 
    }

    public function get_translation($text){
        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post('https://translate.yandex.net/api/v1.5/tr.json/translate?lang=ru-es&key=trnsl.1.1.20191104T103236Z.a59479f96cfec989.059f7b17342bdcc133a95665e26473f57e801cee', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'text' => $text
            ],
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);
        return $response_decoded['text'][0];      
    }
}
