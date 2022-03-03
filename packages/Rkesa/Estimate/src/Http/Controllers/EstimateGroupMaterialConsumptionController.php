<?php

namespace Rkesa\Estimate\Http\Controllers;

use DB;
use Log;
use FPDI;
use Exception;
use UrlSigner;
use Carbon\Carbon;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Service\Models\Service;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\Resource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\Estimate\Models\EstimateChange;
use Rkesa\Estimate\Models\EstimateWorker;
use Rkesa\Estimate\Models\EstimateDocument;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateMaterial;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\EstimateFork\Models\EstimateFork;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineWorker;
use Rkesa\Estimate\Models\EstimateGroupWorker;
use Rkesa\Estimate\Models\EstimateLineCategory;
use Rkesa\Estimate\Models\EstimateGroupPayStage;
use Rkesa\Estimate\Models\EstimateLineSeparator;
use Rkesa\Estimate\Http\Helpers\EstimatePDFCreator;
use Rkesa\Estimate\Http\Helpers\PlanningPDFCreator;
use Rkesa\Estimate\Models\EstimateGroupMaterialConsumption;
use Rkesa\Estimate\Models\EstimateLineFichaResource;

class EstimateGroupMaterialConsumptionController extends Controller
{
    public function index(Request $request)
    {
        $group_id = intval($request->input('group_id', '0'));
        $estimate_id = intval($request->input('estimate_id', '0'));
        $date = Carbon::parse($request->input('date'));

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try{
            
            $estimate_group = EstimateGroup::where('estimate_id', $estimate_id)->where('group_id', $group_id)->first();

            if ($estimate_group == null){
                $estimate_group = new EstimateGroup;
                $estimate_group->group_id = $group_id;
                $estimate_group->estimate_id = $estimate_id;
                $estimate_group->percent = 0;
                $estimate_group->is_subcontract = false;
                $estimate_group->save();
            }

            $estimate_group_material_consumptions = EstimateGroupMaterialConsumption::select($fields_array);

            $res->rows = $estimate_group_material_consumptions->where('date', $date)->where('estimate_group_id', $estimate_group->id)->get();
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
        try{
            
            $estimate_groups_ids = EstimateGroup::where('estimate_id', $estimate_id)->pluck('id');

            $estimate_group_material_consumptions = EstimateGroupMaterialConsumption::select($fields_array);

            $res->rows = $estimate_group_material_consumptions->whereIn('estimate_group_id', $estimate_groups_ids)->get();
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
        try{

            if ($request->exists('estimate_group_material_consumption')) {

                $group_id = intval($request->group_id);
                $estimate_id = intval($request->estimate_id);

                $estimate_group = EstimateGroup::where('estimate_id', $estimate_id)->where('group_id', $group_id)->first();

                EstimateGroupMaterialConsumption::where('date', $request->estimate_group_material_consumption[0]['date'])->where('estimate_group_id', $estimate_group->id)->delete();

                foreach($request->estimate_group_material_consumption as $eg)
                {
                    //if ($eg['id'] == 0){
                        $estimate_group_mat = new EstimateGroupMaterialConsumption;

                        $estimate_group_mat->estimate_group_id = $estimate_group->id;
                        $estimate_group_mat->date = $eg['date'];
                        $estimate_group_mat->resource_id = $eg['resource_id'];
                        $estimate_group_mat->estimate_line_category_id = $eg['estimate_line_category_id'];
                        $estimate_group_mat->estimate_unit_id = $eg['estimate_unit_id'];
                        $estimate_group_mat->quantity = $eg['quantity'];
                        $estimate_group_mat->save();
                    //}
                    // else{
                    //     $estimate_group_mat = EstimateGroupMaterialConsumption::find($eg['id']);

                    //     $estimate_group_mat->estimate_group_id = $estimate_group->id;
                    //     $estimate_group_mat->date = $eg['date'];
                    //     $estimate_group_mat->resource_id = $eg['resource_id'];
                    //     $estimate_group_mat->estimate_line_category_id = $eg['estimate_line_category_id'];
                    //     $estimate_group_mat->estimate_unit_id = $eg['estimate_unit_id'];
                    //     $estimate_group_mat->quantity = $eg['quantity'];
                    //     $estimate_group_mat->save();
                    // }

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
