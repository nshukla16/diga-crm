<?php

namespace App\Http\Controllers;

use App\CompanyInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyInformationController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $ci = CompanyInformation::select($fields_array)->first();

        return response()->json($ci);
    }

    public function save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $vals = $request->all();
            $ci = CompanyInformation::first();
            $ci->fill($vals);
            $ci->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
