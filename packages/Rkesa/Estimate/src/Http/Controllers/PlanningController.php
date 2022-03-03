<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Estimate\Http\Helpers\PlanningPDFCreator;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateLineCategory;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\EstimateWorker;
use Rkesa\Estimate\Models\EstimateLineWorker;
use Rkesa\Estimate\Models\EstimateMaterial;
use Rkesa\Service\Models\Service;
use Illuminate\Support\Facades\Auth;
use App\User;
use Log;
use DB;
use UrlSigner;
use Carbon\Carbon;
use Exception;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\Estimate\Models\EstimatePlanningDetail;

class PlanningController extends Controller
{
    public function get_planning_pdf_link(Request $request, $id)
    {
//        $user = Auth::user();
//        $estimate = Estimate::findOrFail($id);
//        if (!$user->can_with_estimate('read', $estimate)){
//            return response('forbidden', 403);
//        }
        return response()->json(['link' => UrlSigner::sign(env('APP_URL').'/api/plannings/pdf/'.$id, Carbon::now()->addHours(9))]);
    }

    public function show_planning(Request $request, $id)
    {
        if (UrlSigner::validate(url()->full())){
            $estimate = Estimate::find($id);
            $creator = new PlanningPDFCreator;
            $format = $request->input('format', 'pdf');
            switch ($format) {
                case 'html':
                    $result = $creator->render_html($estimate);
                    return Response($result);
                    break;
                case 'pdf':
                    $result =  $creator->render_pdf($estimate);
                    $headers = [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="'.trans('estimate.Planning_file_name').' '.$estimate->get_estimate_number().'.pdf"',
                        'Accept-Ranges' => 'bytes',
                        'Content-Length' => strlen($result)
                    ];
                    return Response($result, 200, $headers);
                    break;
            }
        }else{
            return response('forbidden', 403);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $estimate = Estimate::with(['groups'])->find($id);
        $mat_count = EstimateMaterial::where('estimate_id', '=', $id)->count();
        $unformatted_lines = $estimate->lines_with_join();
        // Help vars
        $formatted_lines = array();
        $category_price = 0;
        $begin_of_estimate = true;
        $materials = array();
        foreach($unformatted_lines as $line){
            $line->line_number = EstimateLine::gen_number($line->id, $unformatted_lines);
            $line->active = false;
            $line->show_insert_after = false;
            $line->children_total_price = 0.0;
            $line = $line->toArray();
            $line['workers'] = array();
            if ($line['lineable_type']=='\App\EstimateLineFicha'){
                $line['maodeobra'] = array();
                $line['materials'] = array();
                $line['equipment'] = array();
                $line['subs'] = array();
                $line['maodeobra']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->where('resource_type', 0)->get()->toArray();
                for ($i = 0; $i < count($line['maodeobra']['list']); $i++){
                    $line['maodeobra']['list'][$i]['total_price'] = 0;
                }
                $line['materials']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->with('resource')->where('resource_type', 1)->get()->toArray();
                for ($i = 0; $i < count($line['materials']['list']); $i++){
                    $line['materials']['list'][$i]['name'] = $line['materials']['list'][$i]['resource']['name'];
                    $line['materials']['list'][$i]['total_price'] = 0;
                    if ($mat_count == 0) {
                        $in = false;
                        foreach ($materials as &$m) {
                            if ($m['resource_id'] == $line['materials']['list'][$i]['resource_id']) {
                                $m['quantity'] += $line['materials']['list'][$i]['quantity'];
                                $in = true;
                            }
                        }
                        if (!$in) {
                            array_push($materials, $line['materials']['list'][$i]);
                        }
                    }
                }

                $line['equipment']['list'] = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->where('resource_type', 2)->get()->toArray();
                for ($i = 0; $i < count($line['equipment']['list']); $i++){
                    $line['equipment']['list'][$i]['total_price'] = 0;
                }
                $line['subs']['list']      = EstimateLineFichaResource::where('estimate_line_ficha_id', $line['lineable_id'])->where('resource_type', 3)->get()->toArray();
                for ($i = 0; $i < count($line['subs']['list']); $i++){
                    $line['subs']['list'][$i]['total_price'] = 0;
                }
                $line['maodeobra']['total_price'] = 0;
                $line['materials']['total_price'] = 0;
                $line['equipment']['total_price'] = 0;
                $line['subs']     ['total_price'] = 0;
            }
            // Line Workers
            // if ($line['lineable_type']=='\App\EstimateLineFicha' || $line['lineable_type']=='\App\EstimateLineData'){
                $line['workers'] = EstimateLineWorker::where('estimate_line_id', '=', $line['id'])->with('user')->get()->toArray();
                foreach($line['workers'] as &$w){
                    $w['name'] = $w['user']['name'];
                    $w['id'] = $w['user_id'];
                    $w['user'] = null;
                }
            // }
            array_push($formatted_lines, $line);
        }
        $estimate->lines = $formatted_lines;
        $estimate->current_active = null;
        $estimate->current_show_insert_after = null;
//        $estimate->e_id = $estimate->id;
        $units = EstimateUnit::all(['id', 'measure']);
        $estimate->service;
        $estimate->workers;
        $estimate = $estimate->toArray();
        foreach($estimate['workers'] as &$w){
            $w['user']['salary'] = $w['price'];
            $w = $w['user'];
        }
        if ($mat_count == 0) {
            $estimate['materials'] = $materials;
        }else{
            $estimate['materials'] = EstimateMaterial::where('estimate_id', '=', $id)->with('resource')->get()->toArray();
            for ($i = 0; $i < count($estimate['materials']); $i++) {
                $estimate['materials'][$i]['name'] = $estimate['materials'][$i]['resource']['name'];
            }
        }
        return view('estimate::planning/edit', ['estimate' => $estimate, 'units' => $units]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $res = (object)array();

        $res->errcode = 0;
        try{
            $estimate = Estimate::find($id);
            $estimate->route_index = $request->route_index;
            $estimate->save();
            $estimate->workers()->delete();
            if ($request->exists('workers')) {
                foreach ($request->workers as $ps) {
                    $worker = new EstimateWorker();
                    $worker->user_id = $ps['id'];
                    $worker->price = $ps['salary'];
                    $worker->estimate_id = $estimate->id;
                    $worker->group_id = $ps['group_id'];
                    $worker->save();
                }
            }

            if ($request->exists('deleted_groups')) {
                foreach ($request->deleted_groups as $g) {
                    $eg = EstimateGroup::where('estimate_id', $estimate->id)->where('group_id', $g)->delete();
                }
            } 

            if ($request->exists('groups')) {
                foreach ($request->groups as $g) {
                    $eg = EstimateGroup::where('estimate_id', $estimate->id)->where('group_id', $g['id'])->first();
                    if ($eg == null){
                        $eg = new EstimateGroup();
                        $eg->estimate_id = $estimate->id;
                        $eg->group_id = $g['id'];
                    }
                    $eg->is_subcontract = $g['is_subcontract'];
                    $eg->percent = $g['percent'];
                    $eg->save();
                }
            } 

            $estimate->estimate_materials()->delete();
            if ($request->exists('materials')) {
                foreach ($request->materials as $m) {
                    $mat = new EstimateMaterial();
                    $mat->estimate_id = $estimate->id;
                    $mat->quantity = $m['quantity'];
                    $mat->resource_id = $m['resource_id'];
                    $mat->price = $m['price'];
                    $mat->save();
                }
            }
            $line_ids = EstimateLine::where('estimate_id', '=', $id)->get(['id'])->toArray();
            EstimateLineWorker::whereIn('estimate_line_id', $line_ids)->delete();
            if ($request->exists('lines_workers')) {
                foreach ($request->lines_workers as $lw) {
                    foreach ($lw['workers'] as $w) {
                        if ($w['user_id'] != null){
                            $line_worker = new EstimateLineWorker();
                            $line_worker->estimate_line_id = $lw['id'];
                            $line_worker->user_id = $w['user_id'];
                            $line_worker->hours = $w['hours'];
                            $line_worker->save();
                        }
                    }
                }
            }
            
            $estimate->estimate_details()->delete();
            if ($request->exists('estimate_details')) {
                $d = new EstimatePlanningDetail();
                $d->estimate_id = $estimate->id;

                $d->days = $request->estimate_details['days'];
                $d->start_point_lat = $request->estimate_details['start_point_lat'];
                $d->start_point_lng = $request->estimate_details['start_point_lng'];
                $d->consumption_per_100_km = $request->estimate_details['consumption_per_100_km'];
                $d->gasoline_price = $request->estimate_details['gasoline_price'];
                // $d->company_percent = $request->estimate_details['company_percent'];
                $d->save();
            }

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}
