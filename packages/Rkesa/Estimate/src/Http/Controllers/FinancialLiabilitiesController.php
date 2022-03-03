<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Estimate\Http\Helpers\EstimatePDFCreator;
use Rkesa\Estimate\Http\Helpers\PlanningPDFCreator;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimateChange;
use Rkesa\Estimate\Models\EstimateDocument;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateLineCategory;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Estimate\Models\EstimateLineSeparator;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineWorker;
use Rkesa\Estimate\Models\EstimateMaterial;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\EstimateWorker;
use Rkesa\Estimate\Models\Resource;
use Rkesa\EstimateFork\Models\EstimateFork;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Service\Models\Service;
use App\GlobalSettings;
use App\Group;
use Illuminate\Support\Facades\Auth;
use Log;
use DB;
use Exception;
use FPDI;
use UrlSigner;
use Carbon\Carbon;

class FinancialLiabilitiesController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{

            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'name');
            if ($sort == ''){ $sort = 'name'; }
            $order = $request->input('order', 'asc');
            if ($order == ''){ $order = 'asc'; }

            $date_from = $request->input('date_from', '');
            if ($date_from != '')
            {
                $date_from = Carbon::now()->subDays(5000);
            }

            $date_to = $request->input('date_to', '');
            if ($date_to != '')
            {
                $date_to = Carbon::now()->addDays(5000);
            }

            $groups = Group::with(['estimate_groups', 'estimate_groups.estimate', 'estimate_groups.estimate.service', 'estimate_groups.estimate_group_pay_stages', 
            'estimate_groups.estimate_group_pay_stages.pay_stage',
            'estimate_groups.estimate_group_pay_stages.pay_stage' => function($query) use ($date_from, $date_to) {
                $query->whereDate('payment_date', '>=', $date_from)->orWhereNull('payment_date')->whereDate('payment_date', '<=', $date_to)->orWhereNull('payment_date');
            }])->whereHas('estimate_groups');

            $group_id = $request->input('group_id', '');
            if ($group_id != '')
            {
                $groups->where('id', $group_id);
            }

            $groups->orderBy($sort, $order);

            $res->total = $groups->count();
            $res->rows = $groups->offset($offset)->limit($limit)->get();
            $res->summary = $this->totals($date_from, $date_to);
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function totals($date_from, $date_to){
        $pay_stages = EstimatePayStage::with(['estimate_group_pay_stages', 'estimate_group_pay_stages.estimate_group', 'estimate'])->
        whereDate('payment_date', '>=', $date_from)->orWhereNull('payment_date')->whereDate('payment_date', '<=', $date_to)->orWhereNull('payment_date')->get();

        $receive_from_client = 0.0;
        $pay_to_subcontractor = 0.0;

        foreach($pay_stages as $ps){           

            foreach($ps->estimate_group_pay_stages as $egps){
                if ($egps->estimate_group == null){
                    continue;
                }
                $pay_to_subcontractor += $ps->estimate->price * ($ps->percent / 100) * ($egps->estimate_group->percent / 100) - $egps->fact_paid;
                $receive_from_client += $ps->estimate->price * ($ps->percent / 100) - $ps->fact_paid;
            }
        }

        return array(
            "receive_from_client" => $receive_from_client,
            "pay_to_subcontractor" => $pay_to_subcontractor,
        );
    }
}
