<?php

namespace App\Http\Controllers;

use Exception;
use App\VatType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VatTypeController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $pc = VatType::all();

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

    public function update_settings(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            if ($request->filled('vat_types')) {
                foreach ($request['vat_types'] as $vt) {
                    $vat_type = new VatType();
                    if ($vt['id'] > 0){
                        $vat_type = VatType::find($vt['id']);
                    }
                    $vat_type->code = $vt['code'];
                    $vat_type->name = $vt['name'];
                    $vat_type->percent = $vt['percent'];
                    $vat_type->save();
                }
            }

            if ($request->filled('removed_vat_types')) {
                foreach ($request['removed_vat_types'] as $rvt) {
                    if ($rvt > 0){
                        $vat_type = VatType::find($rvt);
                        $vat_type->delete();
                    }
                }
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
