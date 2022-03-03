<?php

namespace Rkesa\Expences\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Expences\Models\Expence;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ExpencesController extends Controller
{
    public function index(Request $request)
    {
        $estimate_id = intval($request->input('estimate_id', '0'));
        $service_id = intval($request->input('service_id', '0'));
        $client_contact_id = intval($request->input('client_contact_id', '0'));
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == '') {
            $sort = 'created_at';
        }
        $order = $request->input('order', 'asc');
        if ($order == '') {
            $order = 'asc';
        }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {
            $expences = Expence::with('estimate')->select($fields_array);

            if ($client_contact_id != 0) {
                $expences->where('client_contact_id', $client_contact_id);
            }

            if ($service_id != 0) {
                $expences->where('service_id', $service_id)->orWhereHas('estimate', function ($query) use ($service_id) {
                    $query->where('service_id', $service_id);
                });
            }

            if ($estimate_id != 0) {
                $expences->where('estimate_id', $estimate_id);
            }

            $expences->orderBy($sort, $order);

            $res->total = $expences->count();
            $res->rows = $expences->offset($offset)->limit($limit)->get();
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
        try {
            $expence = new Expence;
            $expence->fill($request->all());
            $expence->save();
            $res->id = $expence->id;
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
        try {
            $expence = Expence::find($id);
            $expence->fill($request->all());
            $expence->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function show(Request $request, $id)
    {
        return Expence::with(['estimate'])->findOrFail($id);
    }

    public function destroy($id)
    {
        $expence = Expence::findOrFail($id);
        $expence->delete();
    }
}
