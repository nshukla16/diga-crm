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

class EstimateGroupWorkerActivityController extends Controller
{
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $activity = new EstimateGroupWorkerActivity;
            $activity->fill($request->all());
            $activity->save();            
            $res->id = $activity->id;
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
        $user = Auth::user();
        $res = (object)array();
        $res->errcode = 0;
        try {
            $activity = EstimateGroupWorkerActivity::with('estimate_group_worker')->find($id);
            if ($activity->estimate_group_worker->user_id !== $user->id) {
                return response()->json(['error' => 'Not authorized.'], 403);
            }
            $activity->fill($request->all());
            $activity->save();
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
        EstimateGroupWorkerActivity::find($id)->delete();
    }
}
