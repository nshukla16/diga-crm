<?php

namespace Rkesa\Calendar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleCalendarController;
use App\Notifications\TaskAssigned;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use Log;
use Exception;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Rkesa\Calendar\Http\Resources\EventResource;
use Rkesa\Calendar\Models\Checklist;
use Rkesa\Calendar\Models\Event;
use App\GlobalSettings;
use DateTime;
use Rkesa\Calendar\Models\EventType;
use Rkesa\CalendarExtended\Models\EventGroup;
use Rkesa\Client\Models\Client;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\Service;
use Rkesa\Service\Models\ServiceStateAction;

include_once base_path('packages/Rkesa/Email/vendor/afterlogic_webmail/libraries/afterlogic/api.php');

class CalendarController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $start = $request->input('start', null);
        $end = $request->input('end', null);
        $user_id = $request->input('user_id', 0);
        $done = $request->input('done', '0');
        $event_type = $request->input('event_type_id', 0);

        $events = Event::with('client_contact.client', 'service', 'client_contact.client_contact_phones', 'client_contact.client_contact_emails', 'project');

        //-----------------------------------------------
        // ADD FIELDS FILTERING TO OPTIMIZE LOADING SPEED
        //-----------------------------------------------

        switch ($user->cando('events', 'read')) {
            case 0:
                $events->where('id', null);
                break;
            case 1:
                $events->where('user_id', $user->id);
                break;
            case 2:
                $events->whereIn('user_id', $user->groupmates_ids());
                break;
            case 3:
                break;
        }

        if (isset($start, $end)) {
            $end = Carbon::parse($end)->addDay()->toDateString();

            $events->whereBetween('start', array($start, $end));
        }
        if ($done == '0' || $done == '1') {
            $events->where('done', boolval($done));
        }
        if ($user_id != 0) {
            $events->where('user_id', $user_id);
        }
        if ($event_type != 0) {
            $events->where('event_type_id', $event_type);
        }
        // EXTENDED CALENDAR
        if (config('modules_enabled')['calendar_extended']) {
            $event_groups = $request->input('groups', []);
            if ($event_groups == '') {
                $event_groups = [];
            }
            if (count($event_groups) != 0) {
                $events->whereIn('event_group_id', $event_groups);
            }
        }
        //
        $events = $events->get();

        foreach ($events as $event) {
            // EXTENDED CALENDAR
            if (config('modules_enabled')['calendar_extended']) {
                $event_date = Carbon::parse($event['start']);
                $event['start'] = Carbon::create($event_date->year, $event_date->month, $event_date->day, $event->event_group_id, $event_date->hour, $event_date->minute);
                if ($event['finish'] != null) {
                    $event_end_date = Carbon::parse($event['finish']);
                    $event['finish'] = Carbon::create($event_end_date->year, $event_end_date->month, $event_end_date->day, $event->event_end_date, $event_end_date->hour, $event_end_date->minute);
                }
            }
        }

        return EventResource::collection($events);
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = User::select(['id', 'name'])->find(Auth::user()->id);
            $ev = new Event;
            $ev->fill($request->all());
            $ev->creator_user_id = $user->id;
            $ev->save();
            if ($ev->user->gc_enabled) {
                $ev->load('event_type');
                $ev->load('client_contact');
                GoogleCalendarController::add_event($ev);
            }
            if ($user->id != $ev->user_id) {
                $ev->load('client_contact.client');
                //                Log::info($ev->makeHidden(['user'])->toArray());
                //                Log::info($user->makeHidden(['roles'])->toArray());
                $ev->user->notify(new TaskAssigned($ev->makeHidden(['user'])->toArray(), $user->makeHidden(['roles'])->toArray()));
            }
            $res->id = $ev->id;
        } catch (Exception $e) {
            if (isset($ev->id)) {
                $ev->delete();
            }
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function show(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $event = Event::with('client_contact.client', 'service', 'client_contact.client_contact_phones', 'client_contact.client_contact_emails', 'project')->find($id);
            $res->event = $event;
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
            $user = Auth::user();
            $event = Event::find($id);
            if (!$user->can_with_event('update', $event) || $event->done) {
                return response('', 403);
            }
            $old_responsible = $event->user_id;
            $event->fill($request->all());
            $event->save();

            if ($event->user->gc_enabled) {
                $event->load('event_type');
                $event->load('client_contact');
                GoogleCalendarController::update_event($event);
            }

            if ($user->id != $event->user_id && $old_responsible != $event->user_id) {
                $event->load('client_contact.client');
                $event->user->notify(new TaskAssigned($event->toArray(), $user->toArray()));
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
        $event = Event::find($id);
        if (!$user->can_with_event('delete', $event)) {
            return response('', 403);
        }

        ClientHistory::where('event_id', $id)->delete();

        if ($event->user->gc_enabled) {
            $event->load('event_type');
            $event->load('client_contact');
            GoogleCalendarController::remove_event($event);
        }

        $event->delete();
    }

    public function action_event(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $action_id = $request->input('action_id', null);
            $service_id = $request->input('service_id', null);
            $global_data = $request->input('global_data', null);
            $action = ServiceStateAction::find($action_id);
            if ($action) {
                $service = Service::find($service_id);
                $e = new Event;
                $e->creator_user_id = Auth::user()->id;
                $e->user_id = $action->event_user_id;
                $e->start = self::get_date_from_type($action->event_date_type);
                $e->client_contact_id = $service->client_contact_id;
                $e->event_type_id = $action->event_type_id;
                $e->service_id = $service->id;
                $e->done = false;
                $replace_from = array('{sent_estimate_numbers}', '{event_start}', '{service_note}');
                $replace_to = array($global_data['sent_estimate_numbers'], $global_data['event_start'], $service->note);
                $e->description = str_replace($replace_from, $replace_to, $action->event_description);
                $e->save();
                $e->event_type;
                $e->user;
                if ($e->user->gc_enabled) {
                    $e->load('event_type');
                    $e->load('client_contact');
                    GoogleCalendarController::add_event($e);
                }
                $res->event = $e;
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function finish(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $event = Event::find($id);
            $user = Auth::user();
            if (!$user->can_with_event('addit', $event) && $event->user_id != $user->id) {
                return response('', 403);
            }

            $event->done = true;
            $event->save();
            if ($event->user->gc_enabled) {
                $event->load('event_type');
                $event->load('client_contact');
                GoogleCalendarController::update_event($event);
            }
            $reason = $request->input('reason', '');
            $hist = new ClientHistory;
            $hist->event_id = $event->id;
            $hist->user_id = Auth::user()->id;
            $hist->client_contact_id = $event->client_contact_id;
            $hist->type_id = 18;
            $hist->message = $reason;
            $hist->save();
            $hist->user;
            $hist->event;
            $hist->event->event_type;
            $res->history = $hist;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public static function get_date_from_type($type)
    {
        $date = new DateTime();
        switch ($type) {
            case 0:
                return $date;
            case 1:
                return $date->modify('+1 day');
            case 2:
                return $date->modify('+2 days');
            case 3:
                return $date->modify('+3 days');
            case 4:
                return $date->modify('+4 days');
            case 5:
                return $date->modify('+5 days');
            case 6:
                return $date->modify('+6 days');
            case 7:
                return $date->modify('+1 week');
            case 8:
                return $date->modify('+1 month');
            case 9:
                return $date->modify('+2 months');
            case 10:
                return $date->modify('+6 months');
            case 11:
                return $date->modify('+1 years');
            case 12:
                return $date->modify('+2 weeks');
            case 13:
                return $date->modify('+3 weeks');
        }
        return null;
    }
}
