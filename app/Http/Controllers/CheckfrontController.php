<?php

namespace App\Http\Controllers;

use App\GlobalSettings;
use Illuminate\Http\Request;
use Log;

class CheckfrontController extends Controller
{
    public static function get_item_by_id($item_id)
    {
        $gs = GlobalSettings::first();

        $checkfront = new CheckfrontAPI(
            array(
                'host'=> $gs->checkfront_host,
                'auth_type' => 'token',
                'api_key'  => $gs->checkfront_api_key,
                'api_secret' => $gs->checkfront_api_secret,
            )
        );

        $item = $checkfront->get('item', array('item_id' => $item_id, 'packages' => 'true'));
        if ($item['request']['status'] == 'OK') {
            return $item['items'][(int)$item_id];
        }else{
            Log::info($item);
            return null;
        }
    }
}
