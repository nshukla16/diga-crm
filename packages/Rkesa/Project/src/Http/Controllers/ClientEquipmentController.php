<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\UserRole;
use App\User;
use Log;
use Exception;
use Rkesa\Project\Models\ClientEquipment;
use Illuminate\Support\Facades\Auth;

class ClientEquipmentController extends Controller
{
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $equipment = new ClientEquipment;
            $equipment->fill($request->all());
            $equipment->save();
            $res->id = $equipment->id;
            $res->created_at = $equipment->created_at->toDateTimeString();
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
            $equipment = ClientEquipment::find($id);
            $equipment->fill($request->all());
            $equipment->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
