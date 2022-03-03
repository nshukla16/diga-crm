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
use Rkesa\Estimate\Models\EstimateLineFichaResource;

class EstimateResourcesController extends Controller
{
    public function index(Request $request)
    {
        $estimate_id = intval($request->input('estimate_id', '0'));

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try{
            
            $estimate_mats = EstimateMaterial::where('estimate_id', $estimate_id)
                ->pluck('resource_id')->toArray();
            
            $estimate_line_ids = EstimateLine::where('estimate_id', $estimate_id)
                ->where('lineable_type', '\App\EstimateLineFicha')
                ->pluck('lineable_id')->toArray();

            $estimate_line_ficha_resources = EstimateLineFichaResource::whereIn('estimate_line_ficha_id', $estimate_line_ids)
                ->pluck('resource_id')->toArray();

            $resources = Resource::select($fields_array);

            $res->rows = $resources
                ->whereIn('id', $estimate_mats)
                ->orWhereIn('id', $estimate_line_ficha_resources)                
                ->distinct()->get();
            
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
