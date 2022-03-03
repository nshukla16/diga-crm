<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\UserRole;
use App\User;
use Log;
use Exception;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\Manufacturer;
use Rkesa\Client\Models\Client;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\ProjectType;
use Rkesa\Project\Models\Equipment;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == ''){ $sort = 'created_at'; }
        $order = $request->input('order', 'asc');
        if ($order == ''){ $order = 'asc'; }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try{
            $equipment = Equipment::select($fields_array)->with('manufacturer');

            $manufacturer_id = $request->input('manufacturer_id', null);
            if ($manufacturer_id){
                $equipment->where('manufacturer_id', $manufacturer_id);
            }

            $equipment->orderBy($sort, $order);

            $res->total = $equipment->count();
            $res->rows = $equipment->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
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
        try{
            $equipment = new Equipment;
            $equipment->fill($request->all());
            $equipment->save();
            $res->id = $equipment->id;
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
            $equipment = Equipment::find($id);
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

    public function show($id)
    {
        $equipment = Equipment::findOrFail($id);

        $equipment->manufacturer_object = ['label' => $equipment->manufacturer->name, 'id' => $equipment->manufacturer->id];

        return $equipment;
    }

    public function destroy($id)
    {
//        $user = Auth::user();
        $equipment = Equipment::findOrFail($id);
//        if (!$user->can_with_client('delete', $client) || $id == 1){ // id == 1 if it is test company
//            return response('', 403);
//        }
//        // Contacts and phones
//        $contact_ids = ClientContact::where('client_id', $client->id)->pluck('id')->toArray();
//        foreach ($contact_ids as $contact_id) {
//            $e = app('Rkesa\Client\Http\Controllers\ContactController')->destroy($request, $contact_id);
//            Log::info($e);
//        }
        $equipment->delete();
    }
}
