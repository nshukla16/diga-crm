<?php

namespace Rkesa\Planning\Http\Controllers;

use App\GlobalSettings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Rkesa\Planning\Models\UserPlanning;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\EstimateFork\Models\EstimateFork;
use Illuminate\Support\Facades\DB;
use Rkesa\Service\Models\Service;

class UserPlanningController extends Controller
{
    public function index(Request $request)
    {       
        $search = $request->input('search', false);
        $user = Auth::user();
        if ($search !== false) {
            $services_master_estimates = Service::select('master_estimate_id')->distinct('master_estimate_id')->pluck('master_estimate_id');
            $estimates = Estimate::with('service', 'estimate_pay_stages', 'gantts')->whereIn('id', $services_master_estimates);

            if ($search != ''){
                $estimates = $estimates->whereHas('service', function($q) use($search) {
                    $q->where('estimate_number', 'like', '%'.$search.'%');
                });
            }
            return response()->json($estimates->limit(20)->get());

         }else {


//        $user = Auth::user();

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
                $plans = UserPlanning::with(['users', 'users.group'])->select($fields_array);

                $plans->orderBy($sort, $order);

                $res->total = $plans->count();
                $res->rows = $plans->offset($offset)->limit($limit)->get();
            } catch (Exception $e) {
                $res->errcode = 1;
                $res->errmess = $e->getMessage();
                Log::channel('daily')->error($e);
                app('sentry')->captureException($e);
            }
            return response()->json($res);
        }
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $plan = new UserPlanning;
            $plan->name = $request['name'];
            $plan->is_custom = $request['is_custom'];
            $plan->save();
            $res->id = $plan->id;
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
        $res = (object)array();
        $res->errcode = 0;
        try {
            $plan = UserPlanning::with('users', 'users.tasks', 'users.tasks.estimate.service', 'users.tasks.estimate.estimate_pay_stages', 'users.tasks.gantt')->find($id);

            $res->plan = $plan;
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
        UserPlanning::findOrFail($id)->delete();
    }

}