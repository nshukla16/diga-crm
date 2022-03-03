<?php

namespace App\Http\Controllers;

use Exception;
use App\InvoiceSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceSeriesController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $pc = InvoiceSeries::all();

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

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $series = new InvoiceSeries();
            $series->fill($request->all());
            $series->increment = 1;
            $series->save();
            $res->id = $series->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
