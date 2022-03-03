<?php

namespace Rkesa\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Rkesa\Hr\Models\KpiUserAndGroup;
use Rkesa\Hr\Models\KpiUserAndGroupType;
use App\User;
use Carbon\Carbon;

class KpiUserAndGroupController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;

        try{
            $fields = $request->input('fields', '*');
            $fields_array = explode(",", $fields);

            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'created_at');
            if ($sort == ''){ $sort = 'created_at'; }
            $order = $request->input('order', 'asc');
            if ($order == ''){ $order = 'asc'; }

            $isGroup = $request->input('isGroup', false);

            $ugs = KpiUserAndGroup::select($fields_array)->with('user', 'group', 'period', 'types', 'types.type');

            if ($isGroup == 'false'){
                $ugs->where('user_id', '!=', null);
            }
            else {
                $ugs->where('group_id', '!=', null);
            }

            $ugs->orderBy($sort, $order);

            $res->total = $ugs->count();
            $res->rows = $ugs->offset($offset)->limit($limit)->get();

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
            $ug = new KpiUserAndGroup;
            $ug->user_id = $request['user_id'] == 0 ? NULL : $request['user_id'];
            $ug->group_id = $request['group_id'] == 0 ? NULL : $request['group_id'];
            $ug->start_date = $request['start_date'];
            $ug->period_id = $request['period_id'];
            $ug->save();
            $res->id = $ug->id;

            foreach ($request['types'] as $t) {
                $type = new KpiUserAndGroupType;
                $type->fill($t);
                $type->kpi_user_and_group_id = $ug->id;
                $type->save();
            }
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
        $ug = KpiUserAndGroup::with(['user', 'group', 'period', 'types', 'types.type'])->findOrFail($id);

        return $ug;
    }

    public function show_by_user_id(Request $request, $id)
    {
        $ug = KpiUserAndGroup::with(['group', 'period', 'types', 'types.type'])->where('user_id', '=', $id)->firstOrFail();
        return $ug;
    }

    public function show_details_by_user_id(Request $request, $id)
    {
        $user = User::where('id', '=', $id)->firstOrFail();
        $kpis = [];

        foreach($request["dates"] as $date)
        {
            array_push($kpis, $user->period_kpi(Carbon::parse($date)));
        }

        return $kpis;
    }

    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $ug = KpiUserAndGroup::find($id);
            $ug->start_date = $request['start_date'];
            $ug->period_id = $request['period_id'];
            $ug->save();

            if ($request->filled('removed_types')) {
                foreach ($request['removed_types'] as $t) {
                    if ($t != 0) {
                        KpiUserAndGroupType::find($t)->delete();
                    }
                }
            }
            foreach ($request['types'] as $t) {
                if ($t['id'] != 0) {
                    $spec = KpiUserAndGroupType::find($t['id']);
                    $spec->fill($t);
                    $spec->save();
                } else {
                    $type = new KpiUserAndGroupType;
                    $type->fill($t);
                    $type->kpi_user_and_group_id = $ug->id;
                    $type->save();
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

    public function destroy($id)
    {
        $ug = KpiUserAndGroup::findOrFail($id);
        $ug->types()->delete();
        $ug->delete();
    }
}
