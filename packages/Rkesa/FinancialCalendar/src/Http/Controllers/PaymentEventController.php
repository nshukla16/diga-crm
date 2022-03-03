<?php

namespace Rkesa\FinancialCalendar\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\FinancialCalendar\Models\PaymentEvent;
use Rkesa\Estimate\Models\EstimatePayStage;

class PaymentEventController extends Controller
{
    public function index(Request $request)
    {

//        $user = Auth::user();

        $start = $request->input('start', null);
        $end = $request->input('end', null);
        $status_id = $request->input('status_id', null);

        $events = PaymentEvent::with('estimate_pay_stage', 'estimate', 'estimate.service', 'client', 'contact', 'service');

        if (isset($start, $end)){
            $events->whereBetween('start', array($start, $end));
        }
        if ($status_id == "null"){
            $events->where('status_id', null);
        }
        if ($status_id > 0){
            $events->where('status_id', $status_id);
        }
        // EXTENDED CALENDAR
        if (config('modules_enabled')['calendar_extended']) {
            $event_groups = $request->input('groups', []);
            if ($event_groups == '') { $event_groups = []; }
            if (count($event_groups) != 0) {
                $events->whereIn('event_group_id', $event_groups);
            }
        }
        return response()->json($events->get());
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $payment_event = new PaymentEvent;
            $payment_event->fill($request->all());
            $payment_event->save();
            $res->id = $payment_event->id;
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
        $res = (object)array();
        $res->errcode = 0;
        try{
            $payment_event = PaymentEvent::find($id);
            $payment_event->fill($request->all());
            $payment_event->save();
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
        $res = (object)array();
        $res->errcode = 0;
        try{
            $event = PaymentEvent::find($id);
            $event->delete();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
    }

    public function disable_email_sending(Request $request){
        $res = (object)array();
        $res->errcode = 0;
        try{
            $payment_event = PaymentEvent::find($request->id);
            if ($payment_event != null){
                $estimate_pay_stage = EstimatePayStage::find($payment_event->estimate_pay_stage_id);
                if ($estimate_pay_stage != null){
                    $estimate_pay_stage->email_template_id = null;
                    $estimate_pay_stage->save();
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
