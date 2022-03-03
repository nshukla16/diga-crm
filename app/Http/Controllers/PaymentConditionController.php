<?php

namespace App\Http\Controllers;

use App\PaymentCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentConditionController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $pc = PaymentCondition::all();

            $res->total = $pc->count();
            $res->rows = $pc;

        } catch (\Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
