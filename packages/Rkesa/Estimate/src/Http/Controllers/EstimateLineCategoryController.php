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

class EstimateLineCategoryController extends Controller
{
    public function index(Request $request)
    {
        $estimate_id = intval($request->input('estimate_id', '0'));

        $estimate_ids = $request->input('estimate_ids', null);
        $estimate_ids_array = [];
        if ($estimate_ids !== null) {
            $estimate_ids_array = explode(",", $estimate_ids);
        }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {

            $estimate_lines = EstimateLine::where('lineable_type', '\App\EstimateLineCategory');

            if ($estimate_id > 0) {
                $estimate_lines = $estimate_lines->where('estimate_id', $estimate_id)->pluck('lineable_id')->toArray();
            }
            if (count($estimate_ids_array) > 0) {
                $estimate_lines = $estimate_lines->whereIn('estimate_id', $estimate_ids_array)->pluck('lineable_id')->toArray();
            }
            $estimate_line_categories = EstimateLineCategory::select($fields_array);
            $res->rows = $estimate_line_categories->whereIn('id', $estimate_lines)->distinct()->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
