<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\EstimateGroupWorkerActivity;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\Estimate\Models\EstimateGroupWorker;
use Auth;

class EstimateGroupWorkersController extends Controller
{
    public function my(Request $request)
    {
        $user = Auth::user();
        $estimate_id = intval($request->input('estimate_id', '0'));
        $date = Carbon::parse($request->input('date'));

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {
            $estimate_group_workers = EstimateGroupWorker::with('estimate_group_workers_activities');
            if ($estimate_id > 0) {
                // $estimate_groups_ids = EstimateGroup::where('estimate_id', $estimate_id)->pluck('id');
                // $estimate_group_workers = $estimate_group_workers->whereIn('estimate_group_id', $estimate_groups_ids);
                $estimate_group_workers = $estimate_group_workers->where('estimate_id', $estimate_id);
            }
            $estimate_group_workers = $estimate_group_workers->select($fields_array);
            $res->rows = $estimate_group_workers->where('date', $date)->where('user_id', $user->id)->get();
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
        $group_id = intval($request->input('group_id', '0'));
        $estimate_id = intval($request->input('estimate_id', '0'));
        $date = Carbon::parse($request->input('date'));

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {

            $estimate_group = EstimateGroup::where('estimate_id', $estimate_id)->where('group_id', $group_id)->first();

            // if ($estimate_group == null){
            //     $estimate_group = new EstimateGroup;
            //     $estimate_group->group_id = $group_id;
            //     $estimate_group->estimate_id = $estimate_id;
            //     $estimate_group->percent = 0;
            //     $estimate_group->save();
            // }

            $estimate_group_workers = EstimateGroupWorker::with('estimate_group_workers_activities')->select($fields_array);

            $res->rows = $estimate_group_workers->where('date', $date)->where('estimate_group_id', $estimate_group->id)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function by_estimate(Request $request)
    {
        $estimate_id = intval($request->input('estimate_id', '0'));

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {

            $estimate_groups_ids = EstimateGroup::where('estimate_id', $estimate_id)->pluck('id');

            $estimate_group_workers = EstimateGroupWorker::with('estimate_group_workers_activities')->select($fields_array);

            $res->rows = $estimate_group_workers->whereIn('estimate_group_id', $estimate_groups_ids)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function update(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {

            if ($request->exists('deleted_estimate_workers')) {
                foreach ($request['deleted_estimate_workers'] as $i) {
                    $r = EstimateGroupWorker::find($i);
                    if ($r) {
                        $r->delete();
                    }
                }
            }

            if ($request->exists('estimate_group_workers')) {

                $group_id = intval($request->group_id);
                $estimate_id = intval($request->estimate_id);
                $estimate_group_id = 0;

                if ($group_id > 0 && $estimate_id > 0) {
                    $estimate_group = EstimateGroup::where('estimate_id', $estimate_id)->where('group_id', $group_id)->first();
                    $estimate_group_id = $estimate_group->id;

                    $all_ids = array_column($request->estimate_group_workers, 'id');

                    $egw_ids = EstimateGroupWorker::where('date', $request->estimate_group_workers[0]['date'])->where('estimate_group_id', $estimate_group->id)->whereNotIn('id', $all_ids)->pluck('id');
                    EstimateGroupWorkerActivity::whereIn('estimate_group_worker_id', $egw_ids)->delete();
                    EstimateGroupWorker::whereIn('id', $egw_ids)->delete();
                }

                foreach ($request->estimate_group_workers as $eg) {
                    if ($eg['user_id'] == 0) {
                        continue;
                    }
                    //if ($eg['id'] == 0){
                    $estimate_group_worker = new EstimateGroupWorker;

                    if ($eg['id'] > 0) {
                        $estimate_group_worker = EstimateGroupWorker::find($eg['id']);
                    }

                    if ($estimate_group_id > 0) {
                        $estimate_group_worker->estimate_group_id = $estimate_group_id;
                    }

                    $estimate_group_worker->user_id = $eg['user_id'];
                    $estimate_group_worker->date = $eg['date'];
                    $estimate_group_worker->date_start_before_lunch = $eg['date_start_before_lunch'];
                    $estimate_group_worker->date_end_before_lunch = $eg['date_end_before_lunch'];
                    $estimate_group_worker->date_start_after_lunch = $eg['date_start_after_lunch'];
                    $estimate_group_worker->date_end_after_lunch = $eg['date_end_after_lunch'];
                    $estimate_group_worker->estimate_id = $eg['estimate_id'];
                    $estimate_group_worker->save();

                    if (!empty($eg['estimate_group_workers_activities'])) {
                        EstimateGroupWorkerActivity::where('estimate_group_worker_id', $estimate_group_worker->id)->delete();
                        foreach ($eg['estimate_group_workers_activities'] as $act) {
                            $activity = new EstimateGroupWorkerActivity();
                            $activity->estimate_group_worker_id = $estimate_group_worker->id;
                            $activity->resource_id = $act['resource_id'];
                            $activity->estimate_line_category_id = $act['estimate_line_category_id'];
                            $activity->estimate_unit_id = $act['estimate_unit_id'];
                            $activity->quantity = $act['quantity'];
                            $activity->save();
                        }
                    }
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
}
