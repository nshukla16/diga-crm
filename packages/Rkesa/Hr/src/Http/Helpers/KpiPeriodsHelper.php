<?php

namespace Rkesa\Hr\Http\Helpers;

use Exception;
use Carbon\Carbon;
use App\GlobalSettings;
use Rkesa\Client\Models\Client;
use Rkesa\Calendar\Models\Event;
use Rkesa\Hr\Models\Timetracker;
use Rkesa\Service\Models\Service;
use Illuminate\Support\Facades\Log;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientHistory;
use Illuminate\Support\Facades\Response;

class KpiPeriodsHelper {

    public function kpi_logic($ug, $endDate)
    {
        if (isset($ug)){
            $period = $ug->period->name;
            
            $temp_date = Carbon::parse($endDate);

            switch($period){
                case "week":
                    $temp_date = $temp_date->addWeek(-1);                        
                    break;
                case "two_weeks":
                    $temp_date = $temp_date->addWeeks(-2);
                    break;
                case "month":
                    $temp_date = $temp_date->addMonth(-1);
                    break;
                case "quarter":
                    $temp_date = $temp_date->addQuarter(-1);
                    break;
                case "year":
                    $temp_date = $temp_date->addYear(-1);                    
                    break;
            }

            return $ug->get_kpi($temp_date, $endDate);            
        }
        return "";
    }

    public function kpi_productivity($startDate, $endDate, $tps, $userIds)
    {
        $tpsWithKpi = $tps->map(function($tp, $key) use($startDate, $endDate, $userIds) {
            $count_fact = 0;
            switch ($tp->type->name)
            {
                case "number_of_finished_tasks_any_type":
                    $count_fact = ClientHistory::
                        whereIn('user_id', $userIds)->
                        where('type_id', '=', 18)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();                                    
                    break;
                case "number_of_finished_tasks_of_special_type":
                    $count_fact = ClientHistory::
                        join('events', 'events.id', '=', 'client_history.event_id')->
                        whereIn('client_history.user_id', $userIds)->
                        where('client_history.type_id', '=', 18)->
                        where('events.event_type_id', '=', $tp->additional_params)->
                        whereBetween('client_history.created_at', [$startDate, $endDate])->
                        count();                    
                    break;
                case "number_of_incoming_calls":
                    $count_fact = ClientHistory::
                        whereIn('user_id', $userIds)->
                        where('type_id', '=', 21)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();
                    break;
                case "number_of_outgoing_calls":
                    $count_fact = ClientHistory::
                        whereIn('user_id', $userIds)->
                        where('type_id', '=', 22)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();
                    break;
                case "number_of_switching_statuses_of_services":
                    $count_fact = ClientHistory::
                        whereIn('user_id', $userIds)->
                        where('type_id', '=', 2)->
                        where('service_state_id', '!=', NULL)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();
                    break;
                case "number_of_sended_emails":
                    $count_fact = ClientHistory::
                        whereIn('user_id', $userIds)->
                        where('type_id', '=', 3)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();
                    break;
                case "number_of_working_time":
                    $time_trackers = Timetracker::
                        whereIn('user_id', $userIds)->
                        whereBetween('start', [$startDate, $endDate])->
                        whereBetween('finish', [$startDate, $endDate])->
                        get();
                    $sum_minutes = 0;
                    foreach($time_trackers as $tt){
                        $sum_minutes += Carbon::parse($tt->finish)->diffInHours($tt->start);
                    }
                    $count_fact = $sum_minutes;
                    break;
                case "number_of_created_contacts":
                    $count_fact = ClientContact::
                        whereIn('creator_user_id', $userIds)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();                    
                    break;
                case "number_of_created_companies":
                    $count_fact = Client::
                        whereIn('creator_user_id', $userIds)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();
                    break;
                case "number_of_created_services":
                    $count_fact = Service::
                        whereIn('creator_user_id', $userIds)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();  
                    break;
                case "number_of_created_tasks_of_any_type":
                    $count_fact = Event::
                        whereIn('creator_user_id', $userIds)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        count();
                    break;
                case "number_of_created_tasks_of_special_type":
                    $count_fact = Event::
                        whereIn('creator_user_id', $userIds)->
                        whereBetween('created_at', [$startDate, $endDate])->
                        where('event_type_id', '=', $tp->additional_params)->
                        count(); 
                    break;
            }

            $tp->setAttribute('count_fact', $count_fact);
            $tp->setAttribute('kpi', $tp->plan_amount == 0 ? 0 : $count_fact / $tp->plan_amount * 100);
            return $tp;
        });        
        return $tpsWithKpi;
    }
}
