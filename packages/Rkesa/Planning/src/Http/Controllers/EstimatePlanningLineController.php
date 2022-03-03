<?php

namespace Rkesa\Planning\Http\Controllers;

use App\GlobalSettings;
use App\Http\Controllers\Controller;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\UserRole;
use App\User;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Planning\Models\EstimatePlanning;
use Rkesa\Planning\Models\EstimatePlanningLine;
use Rkesa\Planning\Models\EstimatePlanningMilestone;

class EstimatePlanningLineController extends Controller
{
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $estimate_line = new EstimatePlanningLine;
//
//            $estimate_line->progress = $request['Progress'];
//            $estimate_line->name = $request['name'];
//            $estimate_line->description = $request['Note'];
//            $estimate_line->predecessor = $request['Predecessor'];
//            $estimate_line->start_datetime = Carbon::parse($request['StartDate']);
//            $estimate_line->end_datetime = Carbon::parse($request['EndDate']);
////            $estimate_line->parent_id = $request['parent_id'];
//
            $estimate_line->fill($request->all());
            $estimate_line->save();

            $res->id = $estimate_line->id;
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
        EstimatePlanningLine::findOrFail($id)->delete();
    }
}
