<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\Connection;
use DB;
use FPDI;
use Exception;
use UrlSigner;
use Carbon\Carbon;
use App\GlobalSettings;
use App\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Service\Models\Service;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\Resource;
use App\Http\Controllers\Controller;
use App\Http\Traits\SaasAuthTrait;
use App\Http\Traits\SaasPayStageTrait;
use App\Http\Traits\SaasServiceTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Rkesa\Client\Models\ClientContact;
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
use Rkesa\Estimate\Models\EstimateLineCategory;
use Rkesa\Estimate\Models\EstimateGroupPayStage;
use Rkesa\Estimate\Models\EstimateLineSeparator;
use Rkesa\Estimate\Http\Helpers\EstimatePDFCreator;
use Rkesa\Estimate\Http\Helpers\PlanningPDFCreator;
use Rkesa\Estimate\Models\EstimateLineFichaResource;
use Rkesa\Planning\Models\UserPlanningUserTask;

class EstimateGroupController extends Controller
{
    use SaasServiceTrait, SaasAuthTrait, SaasPayStageTrait;

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $client_id = $request['client_id'];
            $group_id = intval($request['group_id'], 0);

            $group = Group::where('client_id', $client_id)->firstOrFail();
            if ($group_id > 0) {
                $group = Group::find($group_id);
            }

            $estimate_group = new EstimateGroup;
            $estimate_group->group_id = $group->id;
            $estimate_id = $request['estimate_id'];
            if ($estimate_id > 0) {
                $estimate_group->estimate_id = $estimate_id;
            }
            $estimate_group->service_id = $request['service_id'];

            if ($group->type == 2) {
                $estimate_group->is_subcontract = true;
            }

            $estimate_group->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function all(Request $request)
    {
        $group_id = intval($request->input('group_id', '0'));
        $estimate_id = intval($request->input('estimate_id', '0'));
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == '') {
            $sort = 'created_at';
        }
        $order = $request->input('order', 'asc');
        if ($order == '') {
            $order = 'asc';
        }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {
            $estimate_groups = EstimateGroup::with(['estimate', 'estimate.service', 'estimate.service.client_contact'])->select($fields_array);

            if ($group_id != 0) {
                $estimate_groups->where('group_id', $group_id);
            }

            if ($estimate_id != 0) {
                $estimate_groups->where('estimate_id', $estimate_id);
            }

            $estimate_groups->orderBy($sort, $order);

            $res->total = $estimate_groups->count();
            $res->rows = $estimate_groups->offset($offset)->limit($limit)->get();
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
        return EstimateGroup::with(['estimate', 'estimate.service', 'estimate.service.client_contact', 'estimate.service.client_contact.client_contact_emails', 'estimate.service.client_contact.client_contact_phones'])->findOrFail($id);
    }

    public function index($estimate_id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {

            $estimate_groups = EstimateGroup::with(['estimate_group_pay_stages', 'estimate_group_pay_stages.pay_stage', 'group'])->where('estimate_id', $estimate_id)->get();
            $pay_stages = EstimatePayStage::where('estimate_id', $estimate_id)->get();

            if ($estimate_groups->count() == 0) {
                $user_planning_user_tasks = UserPlanningUserTask::with(['user_planning_user'])->where('estimate_id', $estimate_id)->get();
                foreach ($user_planning_user_tasks as $uput) {
                    $estimate_group = new EstimateGroup;
                    $estimate_group->group_id = $uput->user_planning_user->user_id;
                    $estimate_group->estimate_id = $estimate_id;
                    $estimate_group->percent = $uput->company_percent == null ? 0 : $uput->company_percent;
                    $estimate_group->save();

                    $estimate_groups->push($estimate_group);
                }
            }

            foreach ($estimate_groups as $estimate_group) {
                if ($estimate_group->estimate_group_pay_stages == null || $estimate_group->estimate_group_pay_stages->count() == 0) {
                    foreach ($pay_stages as $pay_stage) {
                        $estimate_group_pay_stage = new EstimateGroupPayStage();
                        $estimate_group_pay_stage->estimate_group_id = $estimate_group->id;
                        $estimate_group_pay_stage->pay_stage_id = $pay_stage->id;
                        $estimate_group_pay_stage->save();
                    }
                    $estimate_group->load(['estimate_group_pay_stages', 'estimate_group_pay_stages.pay_stage']);
                }
            }

            $res->data = $estimate_groups;
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
            $estimate_group = EstimateGroup::with('group.client')->find($id);

            $estimate_group->service_id = $request['service_id'];
            $estimate_group->percent = $request['percent'];
            $estimate_group->work_start = $request['work_start'];
            $estimate_group->work_end = $request['work_end'];
            $estimate_group->contractor_status = $request['contractor_status'];
            $estimate_group->contractor_file = $request['contractor_file'];
            $estimate_group->contractor_file_name = $request['contractor_file_name'];
            $estimate_group->save();

            if ($request->filled('estimate_group_pay_stages')) {
                foreach ($request['estimate_group_pay_stages'] as $ps) {

                    if (array_key_exists('id', $ps) && $ps['id'] > 0) {
                        $pay_stage = EstimateGroupPayStage::find($ps['id']);

                        $pay_stage->text = $ps['text'];
                        $pay_stage->percent = $ps['percent'];
                        $pay_stage->payment_date = $ps['payment_date'];

                        $pay_stage->paid = $ps['paid'];
                        $pay_stage->invoice_number = empty($ps['invoice_number']) ? '' : $ps['invoice_number'];
                        $pay_stage->invoice_file = $ps['invoice_file'];
                        $pay_stage->invoice_file_name = $ps['invoice_file_name'];
                        $pay_stage->fact_paid = $ps['fact_paid'];

                        if ($pay_stage->paid == true) {
                            if ($estimate_group->group->client != null && $estimate_group->group->client->connection_id != null) {
                                $connection = Connection::find($estimate_group->group->client->connection_id);
                                $to_replace = array("http://", "https://", "www.", ".diga.pt");
                                $auth = self::get_access_token();
                                $token = $auth['access_token'];
                                $source_url = str_replace($to_replace, "", env('APP_URL'));

                                self::paid_from_general_contractor(
                                    $connection->url,
                                    $source_url,
                                    $pay_stage->id,
                                    $pay_stage->fact_paid,
                                    $token
                                );
                            }
                        }

                        $pay_stage->save();
                    } else {
                        $pay_stage = new EstimateGroupPayStage;
                        $pay_stage->fill($ps);
                        $pay_stage->invoice_number = empty($ps['invoice_number']) ? '' : $ps['invoice_number'];
                        $pay_stage->save();
                    }
                }
            }

            if ($request->filled('removed_estimate_group_pay_stages')) {
                foreach ($request['removed_estimate_group_pay_stages'] as $r) {
                    if ($r > 0) {
                        $e = EstimateGroupPayStage::find($r);
                        $e->delete();
                    }
                }
            }

            $hist = new ClientHistory;
            $hist->user_id = Auth::user()->id;
            $hist->type_id = 23;
            $hist->service_id = $request->service_id;
            $hist->client_contact_id = $request->client_contact_id;
            $hist->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function change_status(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $estimate_group = EstimateGroup::with(['group.client', 'estimate_group_pay_stages'])->find($id);

            $estimate_group->contractor_status = $request['contractor_status'];
            $estimate_group->contractor_decline_reason = $request['contractor_decline_reason'];

            $service = null;
            $estimate = null;
            $client_contact = null;

            if ($estimate_group->service_id > 0) {
                $service = Service::find($estimate_group->service_id);
                if ($service->master_estimate_id > 0) {
                    $estimate = Estimate::find($service->master_estimate_id);
                }
            } else {
                $estimate = Estimate::find($estimate_group->estimate_id);
                $service = Service::find($estimate->service_id);
            }
            $client_contact = ClientContact::with(['client_contact_emails', 'client_contact_phones'])->find($service->client_contact_id);

            $connection = Connection::find($estimate_group->group->client->connection_id);
            $to_replace = array("http://", "https://", "www.", ".diga.pt");
            $auth = self::get_access_token();
            $token = $auth['access_token'];
            $source_url = str_replace($to_replace, "", env('APP_URL'));

            if ($request['contractor_status'] == 'awaiting_contractor_confirmation') {
                $service->estimate_summ = $request['price'];
                $lines = $estimate->lines_with_join();
                $estimate->lines_raw = $lines;
                $estimate_units = EstimateUnit::all();
                $estimate->estimate_units = $estimate_units;
                $send = self::create_service($connection->url, $source_url, json_encode($service), json_encode($estimate_group), json_encode($estimate), json_encode($client_contact), $token);
            }
            if ($request['contractor_status'] == 'general_contractor_confirmed') {
                $send = self::update_service_status_from_general_contractor($connection->url, $source_url, $service->id, $request['contractor_status'], $request['contractor_decline_reason'], $token);
                //записать в историю
            }
            if ($request['contractor_status'] == 'decline_work_finished') {
                $send = self::update_service_status_from_general_contractor($connection->url, $source_url, $service->id, $request['contractor_status'], $request['contractor_decline_reason'], $token);
                //записать в историю
            }
            if ($request['contractor_status'] == 'work_finished') {
                $send = self::update_service_status_from_general_contractor($connection->url, $source_url, $service->id, $request['contractor_status'], '', $token);
                //записать в историю
            }

            $estimate_group->save();

            $hist = new ClientHistory;
            $hist->user_id = Auth::user()->id;
            $hist->type_id = 1;
            $hist->message = trans('calendar.Service') . ': ' . $service->get_service_number() . ' - ' . $service->name . '. ' . trans('dashboard.status') . ': ' . trans('template.' . $request['contractor_status']);
            if ($request['contractor_status'] == 'decline_work_finished') {
                $hist->message = $hist->message . '. ' . trans('template.reason_of_declining') . ': ' . $request['contractor_decline_reason'];
            }
            $hist->client_contact_id = $client_contact->id;
            $hist->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
