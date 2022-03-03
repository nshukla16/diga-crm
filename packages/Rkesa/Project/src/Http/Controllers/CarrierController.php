<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\UserRole;
use App\User;
use Log;
use Exception;
use Rkesa\Project\Models\Carrier;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\Manufacturer;
use Rkesa\Client\Models\Client;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\ProjectType;
use Rkesa\Project\Models\Equipment;

class CarrierController extends Controller
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
            $carriers = Carrier::with('contracts')->select($fields_array);

            $query = $request->input('query', '');
            if ($query != ''){
                $carriers->where('name', 'like', '%'.$query.'%');
            }

            $carriers->orderBy($sort, $order);

            $res->total = $carriers->count();
            $res->rows = $carriers->offset($offset)->limit($limit)->get();
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
            $carrier = new Carrier;
            $carrier->fill($request->all());
            $carrier->save();
            $res->id = $carrier->id;
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
            $carrier = Carrier::find($id);
            $carrier->fill($request->all());
            $carrier->save();
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
        $carrier = Carrier::with('contracts')->findOrFail($id);

        return $carrier;
    }

    public function destroy($id)
    {
        //
    }
}
