<?php

namespace Rkesa\CalendarExtended\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use Log;
use Exception;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Rkesa\Calendar\Models\Checklist;
use Rkesa\Calendar\Models\Event;
use Rkesa\Calendar\Http\Helpers\ChecklistPDFCreator;
use App\GlobalSettings;
use DateTime;
use Rkesa\Calendar\Models\EventType;
use Rkesa\Client\Models\Client;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\Service;
use Rkesa\Service\Models\ServiceStateAction;

class CalendarExtendedController extends Controller
{

    public function change_for_group(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $user = Auth::user();

            $change = [];
            if ($request['event_group_id'] != 0){ $change['event_group_id'] = $request['event_group_id']; }
            if ($request['event_type_id'] != 0){ $change['event_type_id'] = $request['event_type_id']; }
            if ($request['user_id'] != 0){ $change['user_id'] = $request['user_id']; }
            $events = Event::where('event_group_id', $request['group_id'])->whereDate('start', '=', date($request['group_date']));

            if ($user->cando('events', 'update') == 1) {
                $events->where('user_id', $user->id);
            }
            if ($user->cando('events', 'update') == 2) {
                $events->whereIn('user_id', $user->groupmates_ids());
            }

            $events->update($change);
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

}
