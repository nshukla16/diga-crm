<?php

namespace Rkesa\Project\Http\Controllers;

use App\Events\LegalEntitiesChanged;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\LegalEntity;
use Rkesa\Project\Models\ManufacturerCommissionRelation;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\ProjectManufacturer;

class LegalEntityController extends Controller
{
    public function all(Request $request)
    {
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
            $les = LegalEntity::with('contracts')->select($fields_array);

            $les->orderBy($sort, $order);

            $res->total = $les->count();
            $res->rows = $les->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function index(Request $request)
    {
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
            $les = LegalEntity::with('contracts')->select($fields_array);

            $les->orderBy($sort, $order);

            $res->total = $les->count();
            $res->rows = $les->offset($offset)->limit($limit)->get();
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
        $le = LegalEntity::with('contracts')->find($id);
        return $le;
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $le = new LegalEntity;
            $le->fill($request->all());
            $le->save();

            $res->id = $le->id;

            broadcast(new LegalEntitiesChanged());
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
            $le = LegalEntity::find($id);
            $le->fill($request->all());
            $le->save();

            broadcast(new LegalEntitiesChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function destroy(Request $request, $id)
    {
        if (
            Project::where('seller_legal_entity_id', $id)->count() > 0 ||
            ManufacturerCommissionRelation::where('legal_entity_id', $id)->count() > 0 ||
            ProjectManufacturer::where('inner_seller_legal_entity_id', $id)->count() > 0 ||
            ProjectManufacturer::where('inner_buyer_legal_entity_id', $id)->count() > 0 ||
            ProjectManufacturer::where('buyer_legal_entity_id', $id)->count() > 0
        ) {
            throw new Exception('There are projects with this legal entity. Cannot remove.');
        }

        LegalEntity::find($id)->delete();

        broadcast(new LegalEntitiesChanged());
    }
}
