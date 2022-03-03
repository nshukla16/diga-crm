<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Zadarma\Notify;
use App\Http\Controllers\Zadarma\Settings;
use Exception;

class ZadarmaController extends Controller
{
    /**
     * Handle an incoming Zadarma API Notification Event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function webhook(Request $request)
    {
        if ($request->ip() == '185.45.152.42'){
            if (isset($_GET['zd_echo'])) exit($_GET['zd_echo']);

            $res = (object)array();
            $res->errcode = 0;
            try {
                $settings = Settings::getSettings();
                if($settings->zadarma_enabled == true)
                    $event = new Notify($request->all(), $settings);
            } catch (Exception $e) {
                $res->errcode = 1;
                $res->errmess = $e->getMessage();
                Log::channel('daily')->error($e);
                app('sentry')->captureException($e);
            }
            return response()->json($res);
        }
    }
}
