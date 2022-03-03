<?php

namespace Rkesa\Calendar\Http\Controllers;

use Log;
use Exception;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Rkesa\Calendar\Models\Event;
use App\Http\Controllers\Controller;
use Rkesa\Calendar\Models\EventType;
use App\Events\GlobalSettingsChanged;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Calendar\Events\EventTypesChanged;
use Rkesa\CalendarExtended\Models\EventGroup;

class CalendarSettingsController extends Controller
{

    public function save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            // types
            foreach ($request['types'] as $state) {
                if ($state['id'] == 0) {
                    $event_type = new EventType;
                    $event_type->fill($state);
                    $event_type->save();
                } else {
                    $event_type = EventType::find($state['id']);
                    $event_type->fill($state);
                    $event_type->save();
                }
            }
            foreach ($request['removed'] as $state_id) {
                if ($state_id != 0) {
                    $event_type = EventType::find($state_id);
                    $event_type->events()->delete();
                    $event_type->delete();
                }
            }
            //groups
            if (config('modules_enabled')['calendar_extended']) {
                $groups_data = $request['groups_data'];
                foreach ($groups_data['groups'] as $group) {
                    if ($group['id'] == 0) {
                        $event_group = new EventGroup;
                        $event_group->fill($group);
                        $event_group->save();
                    } else {
                        $event_group = EventGroup::find($group['id']);
                        $event_group->fill($group);
                        $event_group->save();
                    }
                }
                foreach ($groups_data['removed'] as $group_id) {
                    if ($group_id != 1 && $group_id != 0) {
                        $event_group = EventGroup::find($group_id);
                        Event::where('event_group_id', $group_id)->update(['event_group_id' => 1]); // 1 - without group
                        $event_group->delete();
                    }
                }
            }

            $gs = GlobalSettings::first();
            $move_events_to_next_day = $request['move_events_to_next_day'];
            $gs->move_events_to_next_day = $move_events_to_next_day;

            $move_events_to_next_day_time = $request['move_events_to_next_day_time'];
            $gs->move_events_to_next_day_time = $move_events_to_next_day_time;

            $enable_total_by_day_in_calendar = $request['enable_total_by_day_in_calendar'];
            $gs->enable_total_by_day_in_calendar = $enable_total_by_day_in_calendar;

            $enable_service_name_in_event_in_calendar = $request['enable_service_name_in_event_in_calendar'];
            $gs->enable_service_name_in_event_in_calendar = $enable_service_name_in_event_in_calendar;

            $gs->save();

            broadcast(new GlobalSettingsChanged());
            broadcast(new EventTypesChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
