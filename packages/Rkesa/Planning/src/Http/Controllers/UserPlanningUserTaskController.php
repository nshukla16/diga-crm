<?php

namespace Rkesa\Planning\Http\Controllers;

use App\User;
use Exception;
use App\Setting;
use Carbon\Carbon;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Rkesa\Estimate\Models\Estimate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Rkesa\FinancialCalendar\Models\PaymentEvent;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\Estimate\Models\EstimateGroupPayStage;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\Planning\Models\UserPlanningUser;
use Rkesa\Planning\Notifications\PaymentStep;
use Rkesa\Planning\Models\UserPlanningUserTask;
use Rkesa\Planning\Notifications\EstimateGranted;
use Rkesa\Planning\Notifications\TaskChangesInRoadmap;


class UserPlanningUserTaskController extends Controller
{
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = new UserPlanningUserTask;
            $user->fill($request->all());
            $user->save();

            $user_planning_user = UserPlanningUser::find($request->user_planning_user_id);
            $estimate = Estimate::find($request->estimate_id);
            $eg = EstimateGroup::where('estimate_id', $request->estimate_id)->where('group_id', $user_planning_user->user_id)->first();

            if ($estimate != null) {
                $eg = EstimateGroup::where('group_id', $user_planning_user->user_id)->where(function ($query) use ($request, $estimate) {
                    $query->where('estimate_id', $request->estimate_id)
                        ->orWhere('service_id', $estimate->service_id);
                })->first();
            }

            if ($eg != null) {
                $eg->group_id = $user_planning_user->user_id;
                $eg->percent = 100 - $request->company_percent;
                $eg->is_subcontract = $request->is_subcontract;
                $eg->save();
            } else {
                if ($request->estimate_id != null) {
                    $eg = new EstimateGroup();
                    $eg->estimate_id = $request->estimate_id;
                    $eg->service_id = $estimate->service_id;
                    $eg->group_id = $user_planning_user->user_id;
                    $eg->percent = 100 - $request->company_percent;
                    $eg->is_subcontract = $request->is_subcontract;
                    $eg->save();
                }
            }

            $res->id = $user->id;

            if ($request->pay_stages != null) {
                foreach ($request->pay_stages as $ps) {
                    $pay_stage = EstimatePayStage::find($ps['id']);
                    $pay_stage->percent = $ps['percent'];
                    $pay_stage->text = $ps['text'];
                    $pay_stage->estimate_id = $ps['estimate_id'];
                    $pay_stage->payment_date = $ps['payment_date'];
                    $pay_stage->email_template_id = $ps['email_template_id'];
                    $pay_stage->save();

                    $estimate = Estimate::find($ps['estimate_id']);
                    $payment_event = new PaymentEvent;
                    $payment_event->estimate_pay_stage_id = $ps['id'];
                    $payment_event->title = $ps['text'];
                    $payment_event->start = $ps['payment_date'];
                    $payment_event->estimate_id = $ps['estimate_id'];
                    $payment_event->amount = $estimate->price * ($ps['percent'] / 100);
                    //                    $res->id = $payment_event->id;i
                    $payment_event->save();

                    if ($eg != null) {
                        $estimate_group_pay_stage = new EstimateGroupPayStage;
                        $estimate_group_pay_stage->estimate_group_id = $eg->id;
                        $estimate_group_pay_stage->pay_stage_id = $pay_stage->id;
                        $estimate_group_pay_stage->save();
                    }
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

    public function update(Request $request, $id)
    {

        $res = (object)array();
        $res->errcode = 0;
        try {
            $task = UserPlanningUserTask::find($id);

            $estimate = Estimate::with('estimate_pay_stages')->find($task->estimate_id);

            $user_planning_user = UserPlanningUser::find($request->user_planning_user_id);

            $eg = EstimateGroup::where('estimate_id', $request->estimate_id)->where('group_id', $user_planning_user->user_id)->first();

            if ($estimate != null) {
                $eg = EstimateGroup::where('group_id', $user_planning_user->user_id)->where(function ($query) use ($request, $estimate) {
                    $query->where('estimate_id', $request->estimate_id)
                        ->orWhere('service_id', $estimate->service_id);
                })->first();
            }

            if ($eg != null) {
                $eg->percent = 100 - $request->company_percent;
                $eg->is_subcontract = $request->is_subcontract;
                $eg->save();
            } else {
                if ($request->estimate_id != null) {
                    $eg = new EstimateGroup();
                    $eg->estimate_id = $request->estimate_id;
                    $eg->service_id = $estimate->service_id;
                    $eg->group_id = $user_planning_user->user_id;
                    $eg->percent = 100 - $request->company_percent;
                    $eg->is_subcontract = $request->is_subcontract;
                    $eg->save();
                }
            }

            if ($task->estimate_id != null && $request->estimate_id == null) { // when you remove an estimate from task
                foreach ($estimate->estimate_pay_stages as $ps) {
                    PaymentEvent::where('estimate_pay_stage_id', $ps['id'])->delete();
                    $pay_stage = EstimatePayStage::find($ps['id']);
                    $pay_stage->payment_date = null;
                    $pay_stage->save();
                }
            }
            if ($task->estimate_id != $request->estimate_id && $request->estimate_id != null && !empty($task->estimate_id)) { // when you change an estimate
                foreach ($estimate->estimate_pay_stages as $ps) {
                    PaymentEvent::where('estimate_pay_stage_id', $ps['id'])->delete();
                    $pay_stage = EstimatePayStage::find($ps['id']);
                    $pay_stage->payment_date = null;
                    $pay_stage->save();
                }
            }
            $task->fill($request->all());
            $task->save();

            if ($request->pay_stages != null) {
                foreach ($request->pay_stages as $ps) {
                    $estimate_of_pay_stage = Estimate::find($ps['estimate_id']); // Do not erase this one. Otherwise throws an error.
                    $pay_stage = EstimatePayStage::find($ps['id']);
                    $pay_stage->payment_date = $ps['payment_date'];
                    $pay_stage->email_template_id = $ps['email_template_id'];
                    $pay_stage->save();

                    if ($eg != null) {
                        $estimate_group_pay_stage = EstimateGroupPayStage::where('estimate_group_id', $eg->id)->where('pay_stage_id', $pay_stage->id)->first();
                        if ($estimate_group_pay_stage == null) {
                            $estimate_group_pay_stage = new EstimateGroupPayStage;
                            $estimate_group_pay_stage->estimate_group_id = $eg->id;
                            $estimate_group_pay_stage->pay_stage_id = $pay_stage->id;
                            $estimate_group_pay_stage->save();
                        }
                    }

                    $payment_event = PaymentEvent::where('estimate_pay_stage_id', $ps['id'])->first();
                    if (!$payment_event) {
                        $payment_event = new PaymentEvent;
                    }
                    $payment_event->estimate_pay_stage_id = $ps['id'];
                    $payment_event->title = $ps['text'];
                    $payment_event->start = $ps['payment_date'];
                    $payment_event->estimate_id = $ps['estimate_id'];
                    $payment_event->amount = $estimate_of_pay_stage->price * ($ps['percent'] / 100);
                    $payment_event->save();
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

    public function destroy(Request $request, $id)
    {
        $estimate_id = UserPlanningUserTask::findOrFail($id)->estimate_id;
        if ($estimate_id != null) {
            $paystages = EstimatePayStage::where('estimate_id', $estimate_id)->get();
            foreach ($paystages as $ps) {
                PaymentEvent::where('estimate_pay_stage_id', $ps->id)->delete();
            }
        }
        UserPlanningUserTask::findOrFail($id)->delete();
    }

    public function inform_construction_managers(Request $request)
    {

        $res = (object)array();
        $res->errcode = 0;
        try {
            $contruction_managers = Setting::find(17)->value;
            $arr_of_managers = json_decode($contruction_managers);
            foreach ($arr_of_managers as $manager) {
                $estimate_id = $request->estimate_id;
                $estimate_name = $request->estimate_name;
                $roadmap_name = $request->roadmap_name;
                $roadmap_id = $request->roadmap_id;
                User::find($manager->id)->notify(new TaskChangesInRoadmap($estimate_id, $estimate_name, $roadmap_id, $roadmap_name));
            };
            $task = UserPlanningUserTask::find($request->task_id);
            $task->notification_about_changes = 1;
            $task->save();
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
        try {
            $tasks = UserPlanningUserTask::where('estimate_id', $estimate_id)->get();

            $res->rows = $tasks;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
