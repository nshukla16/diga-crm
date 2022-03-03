<?php

namespace App\Http\Controllers;

use Exception;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Helpers\TelegramMadelineProto;

class TelegramController extends Controller
{
    public function send_code(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $tg_helper = new TelegramMadelineProto;
            $res->result = $tg_helper->send_code($request["tg_api_id"], $request["tg_api_hash"], $request["tg_phone"]);
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function enter_code(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $tg_helper = new TelegramMadelineProto;
            $res->result = $tg_helper->enter_code($request["tg_api_id"], $request["tg_api_hash"], $request["tg_phone"], $request["tg_code"]);
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

}
