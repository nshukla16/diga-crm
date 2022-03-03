<?php

namespace Rkesa\Service\Http\Controllers;

use App\Connection;
use DB;
use Auth;
use App\User;
use Exception;
use App\Setting;
use Carbon\Carbon;
use App\GlobalSettings;
use App\Group;
use Box\Spout\Common\Type;
use Illuminate\Http\Request;
use Rkesa\Client\Models\Client;
use Rkesa\Service\Models\Service;
use Box\Spout\Writer\WriterFactory;
use Rkesa\Estimate\Models\Estimate;
use App\Http\Controllers\Controller;
use Rkesa\Service\Models\ServiceType;
use App\Notifications\ServiceAssigned;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\Service\Models\ServicePriority;
use Rkesa\Planning\Models\UserPlanningUser;
use Rkesa\Service\Models\ServiceAttachment;
use App\Http\Traits\ClientAndContactWithTrait;
use App\Http\Traits\SaasAuthTrait;
use App\Http\Traits\SaasServiceTrait;
use Illuminate\Support\Facades\Log;
use Rkesa\Client\Http\Helpers\PlatformHelper;
use Rkesa\Planning\Models\UserPlanningUserTask;
use Rkesa\Estimate\Models\EstimateGroupPayStage;
use Rkesa\Planning\Notifications\EstimateGranted;

//use Rkesa\Planning\src\Notifications\EstimateGranted;
/**
 * @Resource("Services")
 */
class ServiceController extends Controller
{
    use ClientAndContactWithTrait;
    use SaasAuthTrait, SaasServiceTrait;

    public function send_to_platform(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $service = Service::with(['estimate'])->where('id', $id)->firstOrFail();

            if ($service->estimate != null) {
                $service->estimate->lines = $service->estimate->lines_with_join();
            }

            $ph = new PlatformHelper;
            $platform_contract = $ph->transfer_to_platform($service, Auth::user()->sub);
            $service->platform_id = $platform_contract['contractId'];

            $service->save();
            $res->platform_id = $service->platform_id;
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
            $service = Service::find($id);

            $service->contractor_status = $request['contractor_status'];
            $connection = Connection::find($service->connection_id);
            $to_replace = array("http://", "https://", "www.", ".diga.pt");
            $auth = self::get_access_token();
            $token = $auth['access_token'];
            $source_url = str_replace($to_replace, "", env('APP_URL'));

            $send = self::update_service_status_from_contractor($connection->url, $source_url, $service->source_id, $request['contractor_status'], $token);

            $service->save();

            $hist = new ClientHistory;
            $hist->user_id = Auth::user()->id;
            $hist->type_id = 1;
            $hist->message = trans('calendar.Service') . ': ' . $service->get_service_number() . ' - ' . $service->name . '. ' . trans('dashboard.status') . ': ' . trans('template.' . $request['contractor_status']);
            $hist->client_contact_id = $service->client_contact_id;
            $hist->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    private function filter($services, $responsible_user_id, $query, $service_state_id, $client_contact_id)
    {
        $user = Auth::user();

        switch ($user->cando('services', 'read')) {
            case 0:
                $services->where('id', null);
                break;
            case 1:
                $services->where('responsible_user_id', $user->id);
                break;
            case 2:
                $services->whereIn('responsible_user_id', $user->groupmates_ids());
                break;
            case 3:
                break;
        }

        if ($responsible_user_id != 0) {
            $services->where('responsible_user_id', $responsible_user_id);
        }

        if ($service_state_id != 0) {
            $services->where('service_state_id', $service_state_id);
        }

        if ($client_contact_id != 0) {
            $services->where('client_contact_id', $client_contact_id);
        }

        if ($query != '') {
            // parentheses in condition
            $services->where(function ($c) use ($query) {
                $c->where('address', 'like', '%' . $query . '%')
                    ->orWhereHas('client_contact', function ($q) use ($query) {
                        $q->where(DB::raw("CONCAT(`name`, ' ', `surname`)"), 'like', '%' . $query . '%')
                            ->orWhere('name', 'like', '%' . $query . '%')
                            ->orWhere('surname', 'like', '%' . $query . '%');
                    })
                    ->orWhere('estimate_number', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%')
                    ->orWhereHas('service_state', function ($q) use ($query) {
                        $q->where('name', 'like', '%' . $query . '%');
                    });
            });
        }
    }

    /**
     * Show all services
     *
     * Get a JSON representation of available services
     *
     * @Get("/services")
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $responsible_user_id = intval($request->input('responsible_user_id', '0'));
        $service_state_id = intval($request->input('service_state_id', '0'));
        $client_contact_id = intval($request->input('client_contact_id', '0'));
        $sort = $request->input('sort', 'created_at');
        if ($sort == '') {
            $sort = 'created_at';
        }
        $order = $request->input('order', 'asc');
        if ($order == '') {
            $order = 'asc';
        }

        $res = (object)array();
        $res->errcode = 0;
        try {
            $services = Service::with(
                'estimates',
                'client_contact',
                'client_contact.client_contact_phones',
                'responsible_user',
                'client_contact.client',
                'active_events.client_contact',
                'active_events.client_contact.client_contact_phones',
                'active_events.client_contact.client_contact_emails' // part related to tasks column !! can slow down the services page
            );

            $query = $request->input('query', '');
            $this->filter($services, $responsible_user_id, $query, $service_state_id, $client_contact_id);

            $services->orderBy($sort, $order);

            $res->total = $services->count();
            $res->rows = $services->offset($offset)->limit($limit)->get();
        } catch (\Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function get_short(Request $request)
    {
        $client_contact_id = intval($request->input('client_contact_id', '0'));
        $client_id = intval($request->input('client_id', '0'));
        $group = Group::where('client_id', $client_id)->first();

        $res = (object)array();
        $res->errcode = 0;
        try {
            $services = Service::query();

            if ($group !== null) {
                $services = $services->whereHas('groups', function ($query) use ($group) {
                    $query->where('group_id', $group->id);
                })->orWhereHas('estimates', function ($query) use ($group) {
                    $query->whereHas('groups', function ($q) use ($group) {
                        $q->where('group_id', $group->id);
                    });
                });
            } else {
                if ($client_id > 0) {
                    $services = $services->whereHas('client_contact', function ($query) use ($client_id) {
                        $query->where('client_id', $client_id);
                    });
                } else {
                    $services->where('client_contact_id', $client_contact_id);
                }
            }

            $res->rows = $services->get();
        } catch (\Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function by_company_is_group(Request $request)
    {
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

        $responsible_user_id = intval($request->input('responsible_user_id', '0'));
        $service_state_id = intval($request->input('service_state_id', '0'));
        $client_contact_id = intval($request->input('client_contact_id', '0'));
        $client_id = intval($request->input('client_id', '0'));
        $group = Group::where('client_id', $client_id)->first();

        $res = (object)array();
        $res->errcode = 0;
        try {
            $services = Service::with(
                'client_contact.client_contact_phones',
                'client_contact.client_contact_emails',
                'service_type',
                'service_state',
                'attachments',
                'service_priority',
                'checklist_filleds',
                'checklist_filleds.checklist',
                'groups.group',
                'estimates.groups.group',
                'contractor_service_pay_stages'
            );

            $services = $services->whereHas('groups', function ($query) use ($group) {
                $query->where('group_id', $group->id);
            })->orWhereHas('estimates', function ($query) use ($group) {
                $query->whereHas('groups', function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                });
            });

            $query = $request->input('query', '');

            $this->filter($services, $responsible_user_id, $query, $service_state_id, $client_contact_id);

            $services->orderBy($sort, $order);

            $res->total = $services->count();
            $res->rows = $services->offset($offset)->limit($limit)->get();
        } catch (\Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function by_company(Request $request)
    {
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

        $responsible_user_id = intval($request->input('responsible_user_id', '0'));
        $service_state_id = intval($request->input('service_state_id', '0'));
        $client_contact_id = intval($request->input('client_contact_id', '0'));
        $client_id = intval($request->input('client_id', '0'));

        $res = (object)array();
        $res->errcode = 0;
        try {
            $services = Service::with(
                [
                    'client_contact.client_contact_phones',
                    'client_contact.client_contact_emails',
                    'service_type',
                    'service_state',
                    'attachments',
                    'service_priority',
                    'checklist_filleds',
                    'checklist_filleds.checklist',
                    'groups.group',
                    'estimates.groups.group',
                    'contractor_service_pay_stages'
                ]
            );

            $services = $services->whereHas('client_contact', function ($query) use ($client_id) {
                $query->where('client_id', $client_id);
            });

            $query = $request->input('query', '');

            $this->filter($services, $responsible_user_id, $query, $service_state_id, $client_contact_id);

            $services->orderBy($sort, $order);
            $res->total = $services->count();
            $res->rows = $services->offset($offset)->limit($limit)->get();
        } catch (\Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function by_client_contact(Request $request)
    {
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

        $responsible_user_id = intval($request->input('responsible_user_id', '0'));
        $service_state_id = intval($request->input('service_state_id', '0'));
        $client_contact_id = intval($request->input('client_contact_id', '0'));

        $res = (object)array();
        $res->errcode = 0;
        try {
            $services = Service::with(
                'client_contact.client_contact_phones',
                'client_contact.client_contact_emails',
                'attachments',
                'service_priority',
                'service_type',
                'service_state',
                // checklists
                'checklist_filleds',
                'checklist_filleds.checklist',
                'groups.group',
                'estimates.groups.group',
                'contractor_service_pay_stages'
            );
            $services->where('client_contact_id', $client_contact_id);

            $query = $request->input('query', '');

            $this->filter($services, $responsible_user_id, $query, $service_state_id, $client_contact_id);

            $services->orderBy($sort, $order);
            $res->total = $services->count();
            $res->rows = $services->offset($offset)->limit($limit)->get();
        } catch (\Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    // Combine with index
    // Pure mobile app method
    public function find_by_number_and_estimate(Request $request)
    {
        $res = self::estimate_number_parser(request('estimate_number'));
        $estimate_number = $res[0];
        $ad = $res[1];
        $opt = $res[2];
        $rev = $res[3];

        $services = Service::with(['estimates' => function ($q) {
            $q->select(['id', 'option', 'revision', 'service_id']);
        }, 'client_contact.client_contact_phones', 'client_contact.client_contact_emails'])->where('estimate_number', 'like', '%' . $estimate_number . '%');

        if ($ad != null) {
            $services = $services->where('additional', $ad);
        }
        if ($opt != null) {
            $services = $services->whereHas('estimates', function ($q) use ($opt) {
                $q->where('option', $opt);
            });
        }
        if ($rev != null) {
            $services = $services->whereHas('estimates', function ($q) use ($rev) {
                $q->where('revision', $rev);
            });
        }

        return response()->json(['services' => $services->get()], 200, [], JSON_NUMERIC_CHECK);
    }

    //    public function find_by_estimate_number(Request $request)
    //    {
    //        $res = self::estimate_number_parser(request('estimate_number'));
    //        $estimate_number = $res[0];
    //        $ad = $res[1];
    //        $opt = $res[2];
    //        $rev = $res[3];
    //
    //        $estimate = Estimate::with(['service.client_contact.client_contact_phones', 'service.client_contact.client_contact_emails'])->whereHas('service', function($q) use($estimate_number, $ad) {
    //            $q->where('estimate_number', 'like', '%' . $estimate_number . '%')
    //                ->where('additional', $ad);
    //        })->where('option', $opt)->where('revision', $rev)->first();
    //
    //        if ($estimate){
    //            return response()->json($estimate, 200, [], JSON_NUMERIC_CHECK);
    //        }else{
    //            return response()->json(array("message" => "Estimate not found", "error" => "1"), 404);
    //        }
    //
    //    }

    private function estimate_number_parser($estimate_number)
    {
        $ad_delimeter = " " . trans('template.additional');
        $opt_delimeter = " " . trans('template.option');
        $rev_delimeter = " " . trans('template.revision');
        $ad = null;
        $opt = null;
        $rev = null;
        if (strpos($estimate_number, $rev_delimeter) !== false) {
            $rev = intval(substr($estimate_number, strpos($estimate_number, $rev_delimeter) + strlen($rev_delimeter)));
            $estimate_number = substr($estimate_number, 0, strpos($estimate_number, $rev_delimeter));
        }
        if (strpos($estimate_number, $opt_delimeter) !== false) {
            $opt = intval(substr($estimate_number, strpos($estimate_number, $opt_delimeter) + strlen($opt_delimeter)));
            $estimate_number = substr($estimate_number, 0, strpos($estimate_number, $opt_delimeter));
        }
        if (strpos($estimate_number, $ad_delimeter) !== false) {
            $ad = intval(substr($estimate_number, strpos($estimate_number, $ad_delimeter) + strlen($ad_delimeter)));
            $estimate_number = substr($estimate_number, 0, strpos($estimate_number, $ad_delimeter));
        }
        return [$estimate_number, $ad, $opt, $rev];
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();
            $service = Service::create($request->all());
            $service->generate_estimate_number();
            $service->save();

            if ($user->id != $service->responsible_user_id && $service->responsible_user_id != null && $service->responsible_user_id != 0) {
                $service->responsible_user->notify(new ServiceAssigned($service, $user));
            }

            $res->service_id = $service->id;
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
        //        $user = Auth::user();
        $service = Service::with(['region', 'client_contact', 'estimates.estimate_pay_stages', 'estimates.groups.group.client', 'estimates.groups.estimate_group_pay_stages.pay_stage', 'groups.group.client', 'groups.estimate_group_pay_stages.pay_stage'])->find($id);
        //        if (!$user->can_with_service('update', $service)){
        //            return response('', 403);
        //        }

        return $service;
    }

    //    public function edit(Request $request, $id)
    //    {
    //        $user = Auth::user();
    //        $service = Service::find($id);
    //        if (!$user->can_with_service('update', $service)){
    //            return response('', 403);
    //        }
    //
    //        $contact = ClientContact::with(self::$contact_with)->find($service->client_contact_id);
    //
    //        if ($contact->client_id) {
    //            $client = Client::with(self::$client_with)->find($contact->client_id);
    //        }else{
    //            $client = null;
    //        }
    //
    //        return view('client::client_service/edit', [
    //            'client' => json_encode($client),
    //            'contact' => $contact->toJson(),
    //            'users' => User::all()->toJson(),
    //            'priorities' => ServicePriority::all()->toJson(),
    //            'states' => ServiceState::orderBy('order')->with('service_state_actions')->get()->toJson(),
    //            'service' => $service->toJson(),
    //            'service_types' => ServiceType::all()->toJson(),
    //            'from' => json_encode($request['from'])
    //        ]);
    //    }

    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();
            $service = Service::find($id);
            if (!$user->can_with_service('update', $service)) {
                return response('', 403);
            }
            $old_responsible = $service->responsible_user_id;
            $service->fill($request->all());
            $service->save();

            if ($user->id != $service->responsible_user_id && $old_responsible != $service->responsible_user_id && $service->responsible_user_id != null && $service->responsible_user_id != 0) {
                $service->responsible_user->notify(new ServiceAssigned($service, $user));
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
        $user = Auth::user();
        $service = Service::find($id);
        if (!$user->can_with_service('delete', $service)) {
            return response('', 403);
        }

        ClientHistory::where('service_id', $id)->delete();

        $estimate_ids = Estimate::where('service_id', $id)->pluck('id')->toArray();
        foreach ($estimate_ids as $estimate_id) {
            app('Rkesa\Estimate\Http\Controllers\EstimateController')->destroy($request, $estimate_id);
        }
        $service->delete();
    }

    public function export(Request $request)
    {
        $responsible_user_id = intval($request->input('responsible_user_id', '0'));
        $service_state_id = intval($request->input('service_state_id', '0'));
        $query = $request->input('query', '');

        $fileName = 'services-' . date("Y-m-d-H-i-s") . '.xlsx';

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($fileName);
        $writer->addRow([
            trans('service.Service_date'),
            '№',
            trans('service.Service_name'),
            trans('service.SMS_phone'),
            trans('service.Company_name'),
            trans('service.Responsible'),
            trans('client.state'),
            trans('service.Service_client')
        ]);

        $services = Service::with('client_contact.client_contact_phones', 'client_contact.client', 'responsible_user', 'service_state');

        $this->filter($services, $responsible_user_id, $query, $service_state_id, null);

        $services->chunk(1000, function ($services) use ($writer) {
            foreach ($services as $service) {
                $phones = join(',', array_map(function ($e) {
                    return $e['phone_number'];
                }, $service->client_contact->client_contact_phones->toArray()));

                $writer->addRow([
                    $service->created_at->toDateTimeString(),
                    $service->get_service_number(),
                    $service->name,
                    $phones,
                    ($service->client_contact->client_id ? $service->client_contact->client->name : ''),
                    ($service->responsible_user_id ? $service->responsible_user->name : ''),
                    $service->service_state == null ? '' : $service->service_state->name,
                    $service->client_contact->name . ' ' . $service->client_contact->surname
                ]);
            }
        });

        $writer->close();

        return response()->file($fileName)->deleteFileAfterSend(true);
    }

    public function set_new_state(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();
            $service = Service::find($id);
            if (!$user->can_with_service('update', $service)) {
                return response('', 403);
            }

            $service->service_state_id = $request['state_id'];
            $service->save();
            $hist = new ClientHistory;
            $hist->user_id = Auth::user()->id;
            $hist->type_id = 2; //System
            $hist->service_id = $id;
            $hist->service_state_id = $request['state_id'];
            $hist->client_contact_id = $service->client_contact->id;
            if (isset($request['message'])) {
                $hist->message = $request['message'];
            }
            if ($request['state_id'] == '14') {
                $construction_manager = Setting::find(16)->value;
                User::find($construction_manager)->notify(new EstimateGranted($service));
            }

            $gs = GlobalSettings::first();
            if ($gs->default_planning_roadmap_id && $gs->user_planning_service_state_id) {
                if ($gs->user_planning_service_state_id == $request['state_id']) {
                    $estimate = Estimate::find($service->master_estimate_id);
                    $groups = $estimate->groups->all();
                    $pay_stages = $estimate->estimate_pay_stages->all();
                    $deadline_date = Carbon::now()->addDays($estimate->deadline);

                    $days_per_stage = 0;
                    if (count($pay_stages) > 1) {
                        $days_per_stage = floor($estimate->deadline / (count($pay_stages) - 1));
                    }
                    $dt = Carbon::now();

                    for ($i = 0; $i < count($pay_stages); $i++) {
                        $pay_stage = $pay_stages[$i];
                        if ($i == count($pay_stages) - 1) {
                            $pay_stage->payment_date = $deadline_date;
                            $pay_stage->save();
                        } else {
                            $pay_stage->payment_date = $dt;
                            $pay_stage->save();
                        }

                        $dt->addDays($days_per_stage);
                    }

                    if (count($groups) > 0) {
                        for ($i = 0; $i < count($groups); $i++) {
                            $group = $groups[$i];
                            $user_planning_user =
                                UserPlanningUser::where('user_id', $group->group_id)->where('user_planning_id', $gs->default_planning_roadmap_id)->first();
                            if (empty($user_planning_user)) {
                                $user_planning_user = new UserPlanningUser();
                                $user_planning_user->user_id = $group->group_id;
                                $user_planning_user->user_planning_id = $gs->default_planning_roadmap_id;
                                $user_planning_user->color = $this->rand_color();
                                $user_planning_user->content = $group->group->name;
                                $user_planning_user->save();
                            }

                            $existing_planning_task = UserPlanningUserTask::where('user_planning_user_id', $user_planning_user->id)->where('estimate_id', $estimate->id)->first();

                            if ($existing_planning_task == null) {
                                $existing_planning_task = new UserPlanningUserTask();
                                $existing_planning_task->user_planning_user_id = $user_planning_user->id;
                                $existing_planning_task->start = Carbon::now();
                                $existing_planning_task->end = $deadline_date;
                                $existing_planning_task->title = $service->estimate_number;
                                $existing_planning_task->description = $service->address;
                                $existing_planning_task->color = $this->rand_color();
                                $existing_planning_task->estimate_id = $estimate->id;
                                $existing_planning_task->save();
                            } else {
                                $existing_planning_task->start = Carbon::now();
                                $existing_planning_task->end = $deadline_date;
                                $existing_planning_task->title = $service->estimate_number;
                                $existing_planning_task->description = $service->address;
                                $existing_planning_task->save();
                            }
                        }
                    } else {
                        if ($gs->user_planning_user_id) {

                            $existing_planning_task = UserPlanningUserTask::where('user_planning_user_id', $gs->user_planning_user_id)->where('estimate_id', $estimate->id)->first();

                            if ($existing_planning_task == null) {
                                $existing_planning_task = new UserPlanningUserTask();
                                $existing_planning_task->user_planning_user_id = $gs->user_planning_user_id;
                                $existing_planning_task->start = Carbon::now();
                                $existing_planning_task->end = $deadline_date;
                                $existing_planning_task->title = $service->estimate_number;
                                $existing_planning_task->description = $service->address;
                                $existing_planning_task->color = $this->rand_color();
                                $existing_planning_task->estimate_id = $estimate->id;
                                $existing_planning_task->save();
                            } else {
                                $existing_planning_task->start = Carbon::now();
                                $existing_planning_task->end = $deadline_date;
                                $existing_planning_task->title = $service->estimate_number;
                                $existing_planning_task->description = $service->address;
                                $existing_planning_task->save();
                            }
                        }
                    }
                }
            }

            $hist->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    function rand_color()
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    public function additional(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $service_id = $request->input('service', 0);
            $old_service = Service::find($service_id);
            $adds = Service::where('estimate_number', $old_service->estimate_number)->get();
            $last_add = intval($old_service->additional);
            foreach ($adds as $add) {
                if (intval($add->additional) > $last_add) {
                    $last_add = intval($add->additional);
                }
            }
            $gs = GlobalSettings::first();
            $new_service = new Service;
            $new_service->client_contact_id = $old_service->client_contact_id;
            $new_service->responsible_user_id = $old_service->responsible_user_id;
            $new_service->service_state_id = $gs->add_service_state_id;
            $new_service->service_priority_id = $old_service->service_priority_id;
            $new_service->address = $old_service->address;
            $new_service->aru_id = $old_service->aru_id;
            $new_service->estimate_number = $old_service->estimate_number;
            $new_service->service_type_id = $old_service->service_type_id;
            $new_service->name = $old_service->name;
            $new_service->note = $old_service->note;
            $new_service->additional = $last_add + 1;
            $new_service->save();
            $new_service->client_contact;
            $new_service->responsible_user;
            $new_service->service_priority;
            $new_service->service_state;
            $new_service->attachments;
            $new_service->checklist_filleds;
            $res->service = $new_service;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function attachment(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();
            $service = Service::find($id);
            if (!$user->can_with_service('update', $service)) {
                return response('', 403);
            }

            if ($request['attachment']) {
                $attachment = $request['attachment'];
                $service_attachment = new ServiceAttachment;
                $service_attachment->name = $attachment['name'];
                // Copy new
                $old_file = substr($attachment['file'], 1);
                $filename = pathinfo($attachment['file'], PATHINFO_BASENAME);
                $new_file = 'img/uploads/service/' . $filename;
                rename($old_file, $new_file);
                $service_attachment->file = '/' . $new_file;
                $service_attachment->service_id = $id;
                $service_attachment->save();
                $res->file = $service_attachment->file;
                $res->id = $service_attachment->id;
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function remove_attachment(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            ServiceAttachment::find($request['id'])->delete();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
