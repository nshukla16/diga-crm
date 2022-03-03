<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\UserRole;
use App\User;
use Log;
use Exception;
use Rkesa\Project\Models\CarrierContract;

class CarrierContractController extends Controller
{
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $contract = new CarrierContract;
            $contract->fill($request->all());
            $contract->save();
            $res->id = $contract->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $contract = CarrierContract::find($id);
            $contract->fill($request->all());
            $contract->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
