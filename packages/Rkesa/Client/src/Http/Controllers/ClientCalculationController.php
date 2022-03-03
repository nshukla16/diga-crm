<?php

namespace Rkesa\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User as User;
use Illuminate\Http\Request;
use Rkesa\Client\Models\ClientCalculation;
use Rkesa\Service\Models\Aru;

use Exception;
use Log;

class ClientCalculationController extends Controller
{
    public function store(Request $request){
        $res = (object)array();
        $res->errcode = 0;
        try{
            $calculation = ClientCalculation::create($request->all());
            $calculation->save();
            $res->id = $calculation->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
    public function update(Request $request, $id){
        $res = (object)array();
        $res->errcode = 0;
        try{
            $calculation = ClientCalculation::find($id);
            $calculation->calculation_name = $request['calculation_name'];

            $calculation->calculation_file_name = $request['calculation_file_name'];
            $calculation->calculation_file_path = $request['calculation_file_path'];
//            $milestone->datetime = Carbon::parse($request['day']);
//            $milestone->name = $request['name'];
            $calculation->save();
//            $res->id = $calculation->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
//            Log::channel('daily')->error($e);
//            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
    public function destroy(Request $request, $id){
        ClientCalculation::findOrFail($id)->delete();
    }
}