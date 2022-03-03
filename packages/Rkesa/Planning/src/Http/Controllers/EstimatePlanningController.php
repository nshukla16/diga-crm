<?php

namespace Rkesa\Planning\Http\Controllers;

use App\User;
use Exception;
use App\Setting;
use App\UserRole;
use Carbon\Carbon;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EstimatePlanningExport;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Planning\Models\EstimatePlanning;
use Rkesa\Planning\Models\EstimatePlanningLine;
use Rkesa\Planning\Models\EstimatePlanningMilestone;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstimatePlanningController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

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
            $plans = EstimatePlanning::with('estimate.service')->select($fields_array);

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

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $plan = new EstimatePlanning;
            $plan->fill($request->except('estimate_date_start'));
            $plan->start_time = Setting::where('key', 'planning_working_hours_start')->first()->value;
            $plan->end_time = Setting::where('key', 'planning_working_hours_end')->first()->value;
            $plan->save();
            if($request -> estimate_id !== null){
                $unformatted_lines = $plan->estimate->lines_with_join();
                $key_pairs = [];
                $prevous_line_end_date = Carbon::parse($request['estimate_date_start']);
                $prevous_line_end_date->hour = 8;
                $prevous_line_end_date->minute = 0;

                foreach($unformatted_lines as $line) {
                    if ($line->lineable_type != "\\App\\EstimateLineSeparator") {
                        $estimate_planning_line = new EstimatePlanningLine;
                        $estimate_planning_line->estimate_line_id = $line->id;
                        $estimate_planning_line->estimate_planning_id = $plan->id;
                        $estimate_planning_line->line_number = EstimateLine::gen_number($line->id, $unformatted_lines);
                    
                        if ($line->estimate_line_workers->isNotEmpty())
                        {
                            $estimate_planning_line->start_datetime = Carbon::parse($prevous_line_end_date);

                            $max_hours = $line->estimate_line_workers->max('hours');
                            $number_of_days = ceil( $max_hours / (float) 9); 
                            
                            $estimate_planning_line->end_datetime = Carbon::parse($prevous_line_end_date)->addDays($number_of_days);

                            $prevous_line_end_date = $prevous_line_end_date->addDays($number_of_days + 1);
                        }
                        else
                        {
                            if ($unformatted_lines->contains('parent_id', $line->id))
                            {
                                $estimate_planning_line->start_datetime = Carbon::now();
                                $estimate_planning_line->end_datetime = Carbon::now()->addDay(1);
                            }
                            else{
                                $estimate_planning_line->start_datetime = Carbon::parse($prevous_line_end_date);
                                $estimate_planning_line->end_datetime = Carbon::parse($prevous_line_end_date)->addDays(1);
                                $prevous_line_end_date = $prevous_line_end_date->addDays(1);
                            }
                            
                        }                        

                        $estimate_planning_line->progress = 0;
                        $estimate_planning_line->order_number = $line->order;
                        if ($line->parent_id) {
                            $estimate_planning_line->parent_id = $key_pairs[$line->parent_id];
                        }


                        if ($line->lineable_type == "\\App\\EstimateLineCategory") {
                            $estimate_planning_line->name = $line->category_name;
                        } else if ($line->lineable_type == "\\App\\EstimateLineData") {
                            $estimate_planning_line->name = $line->data_description;
                        } else if ($line->lineable_type == "\\App\\EstimateLineSeparator") {
                            $estimate_planning_line->name = $line->separator_name;
                        } else if ($line->lineable_type == "\\App\\EstimateLineFicha") {
                            $estimate_planning_line->name = $line->ficha_description;
                        }

                        $note = '';
                        if ($line->lineable_type === "\\App\\EstimateLineData") {
                            $note = $line->data_note;
                        } else if ($line->lineable_type === "\\App\\EstimateLineFicha") {
                            $note = $line->ficha_note;
                        }
                        $estimate_planning_line->description = $note;
                        $estimate_planning_line->save();

                        $key_pairs[$line->id] = $estimate_planning_line->id;
//                    Log::error($key_pairs);
                    }
                }
            }
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
        $plan = EstimatePlanning::with(['estimate', 'estimate_planning_lines', 'estimate_planning_lines.estimate_line', 'estimate_planning_lines.estimate_line.estimate_line_workers', 'estimate_planning_lines.estimate_line.estimate_line_workers.user'])->findOrFail($id);
        return $plan;
    }

    public function update_task(Request $request, $id) // id of EstimatePlanningLine
    {
//        Log::info($request);
        $line = EstimatePlanningLine::find($id);
        $line->progress = $request['Progress'];
        $line->name = $request['name'];
        $line->description = $request['Note'];
        $line->predecessor = $request['Predecessor'];
        $line->start_datetime = Carbon::parse($request['StartDate']);
        $line->end_datetime = Carbon::parse($request['EndDate']);
        if($request['parent_id'] === ''){
            $line->parent_id = null;
        } else {
            $line->parent_id = $request['parent_id'];
        }
        $line->order_number = $request['order_number'];
//
//        $parent_id = $request->input('parent_id', null) || null;
//        $line->parent_id = $parent_id;
        if($request['parent_id'] === ''){
            $line->parent_id = null;
        } else {
            $line->parent_id = $request['parent_id'];
        }

        $line->save();
    }

    public function destroy(Request $request, $id)
    {
        EstimatePlanning::findOrFail($id)->delete();
    }

    public function store_milestone(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $milestone = new EstimatePlanningMilestone;
            $milestone->datetime = Carbon::parse($request['day']);
            $milestone->name = $request['name'];
            $milestone->estimate_planning_id = $request['estimate_planning_id'];
//            $milestone->fill($request->all());
            $milestone->save();

            $res->id = $milestone->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function show_milestone(Request $request, $id)
    {
//        $plan = EstimatePlanningMilestone::with(['estimate', 'estimate_planning_lines.estimate_line.correct_lineable'])->findOrFail($id);
        $plan = EstimatePlanningMilestone::where('estimate_planning_id', '=', $id)->get();

        return $plan;
    }

    public function update_milestone(Request $request, $id)
    {
        $milestone = EstimatePlanningMilestone::find($id);
        $milestone->datetime = Carbon::parse($request['day']);
        $milestone->name = $request['name'];
        $milestone->save();
    }

    public function destroy_milestone(Request $request, $id)
    {
        EstimatePlanningMilestone::findOrFail($id)->delete();
    }

    public function update_work_hours(Request $request, $id)
    {
        $work_time = EstimatePlanning::find($id);
        $work_time->start_time = $request['start_time'];
        $work_time->end_time = $request['end_time'];
        $work_time->save();
    }

    public function export_to_excel(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $ep = EstimatePlanning::with('estimate_planning_lines')->find($id);
            foreach($ep->estimate_planning_lines as $epl){
                $epl->start_datetime = Carbon::parse($epl->start_datetime)->toIso8601String();
                $epl->end_datetime = Carbon::parse($epl->end_datetime)->toIso8601String();
            }

            $gs = GlobalSettings::first();            
            $model = (object)array();
            $model->estimate_planning = $ep;
            $model->translations = [
                'company_name' => $gs->site_name,
                'project_start' => trans('estimate.Work_start_date', [], $gs->default_language),
                'today' => trans('calendar.Today', [], $gs->default_language),
                'task' => trans('client.task', [], $gs->default_language),
                'progress' => trans('gantt.Progress', [], $gs->default_language),
                'start' => trans('estimate.Start_time', [], $gs->default_language),
                'end' => trans('estimate.Finish_time', [], $gs->default_language),
                'locale' => $gs->default_language,
            ];

            $client = new \GuzzleHttp\Client([
                'headers' => [ 'Content-Type' => 'application/json' ]
            ]);
            $file_name = storage_path('Gantt chart-'.uniqid().'.xlsx');
            $response = $client->post(env('EXCEL_EXPORT_ENDPOINT', '127.0.0.1:8049').'/export', 
                [
                    'body' => json_encode($model),
                    'sink' => $file_name
                ]
            );

            return response()->download($file_name)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);        
    }
}