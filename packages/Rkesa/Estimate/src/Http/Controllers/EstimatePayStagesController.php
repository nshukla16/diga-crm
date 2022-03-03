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
use Rkesa\FinancialCalendar\Models\PaymentEvent;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Estimate\Models\EstimateChange;
use Rkesa\Estimate\Models\EstimateWorker;
use Rkesa\Estimate\Models\EstimateDocument;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateMaterial;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\EstimateFork\Models\EstimateFork;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineWorker;
use Rkesa\Estimate\Models\EstimateLineCategory;
use Rkesa\Estimate\Models\EstimateLineSeparator;
use Rkesa\Estimate\Http\Helpers\EstimatePDFCreator;
use Rkesa\Estimate\Http\Helpers\PlanningPDFCreator;
use Rkesa\Estimate\Models\EstimateLineFichaResource;

class EstimatePayStagesController extends Controller
{
    public function show(Request $request, $id)
    {
        return EstimatePayStage::with(['estimate.service.client_contact'])->findOrFail($id);
    }

    public function update(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{

            if ($request->exists('estimate_pay_stages')) {
                foreach ($request->estimate_pay_stages as $ps) {
                    $pay_stage = EstimatePayStage::find($ps['id']);

                    $pay_stage->invoice_file = $ps['invoice_file'];
                    $pay_stage->invoice_file_name = $ps['invoice_file_name'];
                    $pay_stage->recibo_file = $ps['recibo_file'];
                    $pay_stage->recibo_file_name = $ps['recibo_file_name'];
                    $pay_stage->paid = $ps['paid'];
                    $pay_stage->invoice_number = $ps['invoice_number'];
                    $pay_stage->fact_paid = $ps['fact_paid'];
                    $pay_stage->email_template_id = $ps['email_template_id'];
                    $pay_stage->proof_file = $ps['proof_file'];
                    $pay_stage->proof_file_name = $ps['proof_file_name'];
                    $pay_stage->save();

                    if ($ps['paid'] === true){
                        $estimate_id = $pay_stage->estimate_id;
                        $payment_events = 
                            PaymentEvent::where('estimate_pay_stage_id', $pay_stage->id)->get();
                        foreach($payment_events as $pe){
                            // $pe->delete();
                            $pe->status_id = 1;
                            $pe->save();
                        }

                        $payment_events_by_name = PaymentEvent::
                            where('estimate_id', $pay_stage->estimate_id)->where('title', $pay_stage->text)->get();
                        foreach($payment_events_by_name as $pe){
                            // $pe->delete();
                            $pe->status_id = 1;
                            $pe->save();
                        }
                    }
                }

                $hist = new ClientHistory;
                $hist->user_id = Auth::user()->id;
                $hist->type_id = 24;
                $hist->service_id = $request->service_id;
                $hist->client_contact_id = $request->client_contact_id;
                $hist->save();
            }

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function attach_invoice(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{

            if ($request->exists('estimate_pay_stage')) {
                $pay_stage = EstimatePayStage::find($request['estimate_pay_stage']['id']);
                $pay_stage->invoice_file = $request['invoice_file'];
                $pay_stage->invoice_file_name = $request['invoice_file_name'];
                $pay_stage->invoice_number = $request['invoice_number'];
                $pay_stage->save();

                $hist = new ClientHistory;
                $hist->user_id = Auth::user()->id;
                $hist->type_id = 24;
                $hist->service_id = $request->service_id;
                $hist->client_contact_id = $request->client_contact_id;
                $hist->save();
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
