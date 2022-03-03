<?php

namespace App\Http\Controllers;

use App\MovementType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MovementTypeController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $pc = MovementType::all();

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
