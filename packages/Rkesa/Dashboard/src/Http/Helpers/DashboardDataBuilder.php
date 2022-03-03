<?php

namespace Rkesa\Dashboard\Http\Helpers;

use DB;
use Log;
use DateTime;
use DateInterval;
use Carbon\Carbon;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Rkesa\Client\Models\Client;
use Rkesa\Calendar\Models\Event;
use Rkesa\Service\Models\Service;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Client\Models\ClientReferrer;
use Rkesa\Dashboard\Models\DashboardWidget;
use Rkesa\Service\Models\ServiceStateAction;

class DashboardDataBuilder {
    const UNDEFINED = 'n\a';
    private $request;
    private $master_sum_index;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function get_master_sum_index(){
        return $this->master_sum_index;
    }

    public function build_entity($build_type) {
        $rows = array();

        $post = $this->request->all();
        $post_fields = $post['fields'];
        $responsible = $post['responsible'];
        $status_id = $post['status_id'];
        $range = array($post['range']['start']['date'], $post['range']['end']['date']);

        $service_conditions = array();
        $service_fields = array('id','responsible_user_id', 'client_contact_id', 'master_estimate_id');
        $service_relations = array();

        if($build_type == 'short')
            $total = config('dashboard.stage.rows.count');
        else $total = false;

        $config_fields = config('dashboard.entity.fields.columns');

        $fields = array();
        $this->master_sum_index = null;

        foreach($post_fields as $i => $field) {
            $key = array_search($field['type'], array_column($config_fields, 'value'));

            if(isset($config_fields[$key]))
                array_push($fields, array(
                    'id'   => $field['id'],
                    'e_id' => $field['dashboard_entity_id'],
                    'type' => $config_fields[$key]['value'],
                    'text' => $config_fields[$key]['text'],
                    'event_type_id' => $field['event_type_id']
                ));
            if ($config_fields[$key]['text'] == 'master_sum'){
                $this->master_sum_index = $i;
            }
        }

        array_push($service_relations, 'client_contact');
        array_push($service_relations, 'responsible_user');

        foreach($fields as $field) {
            switch($field['type']) {
                case 1:
                    break;
                case 2:
                    array_push($service_fields, 'law_number');
                    break;
                case 3:
                    array_push($service_fields, 'work_initial_date');
                    break;
                case 4:
                    array_push($service_fields, 'estimate_summ');
                    break;
                case 5:
                    array_push($service_fields, 'estimate_number');
                    array_push($service_fields, 'additional');
                    break;
                case 6:
                    break;
                case 7:
                    array_push($service_relations, 'client_contact.client_referrer');
                    break;
                case 8:
                    break;
                case 9:
                    array_push($service_fields, 'address');
                    break;
                case 10:
                    array_push($service_relations, 'events');
                    break;
                case 11:
                    break;
                case 12:
                    array_push($service_relations, 'events');
                    break;
                case 13:
                    array_push($service_relations, 'events');
                    break;
            }
        }

        if($responsible != 0)
            array_push($service_conditions, ['responsible_user_id', '=', $responsible ]);

        array_push($service_conditions, ['service_state_id', '=', $status_id]);

        $services = Service::where($service_conditions)
            ->with($service_relations)
            ->select($service_fields);

        if ($post['use_range']) {
            $services = $services->whereBetween('created_at', $range);
        }

        if($total)
            $services->take($total);

        $services = $services->get();

        foreach($services as $service) {
            $row = array();
            $val = '';
            array_push($row, $service->client_contact->id);
            foreach($fields as $field) {
                switch($field['type']){
                    case 1:
                        $history = ClientHistory::where(
                            'id', '=', ClientHistory::where('service_id', '=', $service->id)->whereBetween('created_at', $range)->max('id')
                        )->first();
                        if($history)
                            $val = $history->created_at->format('d.m.Y H:i:s');
                        else $val = self::UNDEFINED;
                        break;
                    case 2: // NOT USED
                        $val = $service->law_number ? $service->law_number : self::UNDEFINED;
                        break;
                    case 3: // NOT USED
                        $val = $service->work_initial_date ? $service->work_initial_date : self::UNDEFINED;
                        break;
                    case 4:
                        $val = $service->estimate_summ ? $service->estimate_summ : self::UNDEFINED;
                        break;
                    case 5:
                        if ($service->master_estimate_id != null){
                            $estimate = Estimate::with('service')->find($service->master_estimate_id);
                            if ($estimate){
                                $val = $estimate->get_estimate_number();
                            }else{
                                $val = $service->get_service_number();
                            }
                        }else{
                            $val = $service->get_service_number();
                        }
                        break;
                    case 6:
                        $c = $service->client_contact;
                        $val = $c->name.' '.$c->surname;
                        break;
                    case 7:
                        if ($service->client_contact->client_referrer) {
                            $val = $service->client_contact->client_referrer->title;
                        }else{
                            $val = self::UNDEFINED;
                        }
                        break;
                    case 8:
                        if($service->responsible_user)
                            $val = $service->responsible_user->name ? $service->responsible_user->name : self::UNDEFINED;
                        else $val = self::UNDEFINED;
                        break;
                    case 9:
                        $address_parts = explode(",", $service->address);
                        $address_parts_count = count($address_parts);

                        $val = self::UNDEFINED;
                        switch($address_parts_count){
                            case 2:
                                $val = $address_parts[$address_parts_count-1];
                                break;
                            case 3:
                                $val = $address_parts[$address_parts_count-2];
                                break;
                        }
                        break;
                    case 10:
                        $event = $service->events->where('done', false)->sortBy('start')->values()->first();
                        if($event) {
                            $val = $event->user->name;
                        } else {
                            $val = self::UNDEFINED;
                        }
                        break;
                    case 11: // NOT USED
                        $val = $service->work_final_date ? $service->work_final_date : self::UNDEFINED;
                        break;
                    case 12:
                        $event = $service->events->where('event_type_id', $field['event_type_id'])->where('done', false)->sortBy('start')->values()->first();
                        if ($event) {
                            $val = $event->start->format('d.m.Y H:i:s');
                        }else{
                            $val = self::UNDEFINED;
                        }
                        break;
                    case 13:
                        $event = $service->events->where('event_type_id', $field['event_type_id'])->where('done', false)->sortBy('start')->values()->first();
                        if ($event) {
                            $val = $event->user->name;
                        }else{
                            $val = self::UNDEFINED;
                        }
                        break;
                }
                array_push($row, $val);
            }
            array_push($rows, $row);
        }

        return $rows;
    }

    public function build_widget() {
        $gs = GlobalSettings::first();
        $currency_symbol = currency()->getCurrency($gs->currency)['symbol'];

        $series = array();
        $additional_data = array();
        $categories = array();
        $x_title = '';
        $y_title = '';
        $funnel_items = [];
        $rejected_count = 0;
        $time_to_reject = 0;
        $time_to_sold = 0;
        $cities_result = [];
        $service_addresses = [];

        $post = $this->request->all();
        $range = array($post['range']['start']['date'], $post['range']['end']['date']);
        $widget = DashboardWidget::find($post['id']);

        switch($widget->data_type) {
            case 1: // states
                $services = ServiceState::select('service_states.name', DB::raw('COUNT(service_state_id) as y'))
                    ->leftJoin('services', function($join) use ($range, $post) {
                        $join->on('services.service_state_id', '=', 'service_states.id');
                        if (array_key_exists('use_range', $post) && $post['use_range']){
                            $join->whereBetween('services.created_at', $range);
                        }
                    })
                    ->where('service_states.type', 0)
                    ->where('service_states.deleted_at', null)
                    ->groupBy('service_states.id')
                    ->orderBy('y', 'DESC')
                    ->get();

                $series = [[
                    'name' => trans('dashboard.statuses'),
                    'data' => $services
                ]];

                $total_count = 0;
                foreach($services as $service){
                    $total_count += $service->y;
                }
                $additional_data['total_count'] = $total_count;

                $categories = array_map(function($s){
                    return $s['name'];
                }, $services->toArray());

                $x_title = trans('dashboard.status');
                $y_title = trans('dashboard.quantity');
                break;
            case 2: // contacts/clients referrers
                $clients = ClientReferrer::select('client_referrers.title as name', DB::raw('COUNT(client_referrer_id) as y'))
                    ->leftJoin('client_contacts', function($join) use ($range, $post) {
                        $join->on('client_contacts.client_referrer_id', '=', 'client_referrers.id');
                        if (array_key_exists('use_range', $post) && $post['use_range']){
                            $join->whereBetween('client_contacts.created_at', $range);
                        }
                    })
                    ->groupBy('client_referrers.id')
                    ->orderBy('y', 'DESC')
                    ->get();

                $series = [[
                    'name' => trans('dashboard.referrers'),
                    'data' => $clients
                ]];

                $total_count = 0;
                foreach($clients as $service){
                    $total_count += $service->y;
                }
                $additional_data['total_count'] = $total_count;

                $categories = array_map(function($s){
                    return $s['name'];
                }, $clients->toArray());

                $x_title = trans('dashboard.referrer');
                $y_title = trans('dashboard.quantity');
                break;
            case 3: // avg setting status time
                $services = ClientHistory::select(DB::raw('YEAR(services.created_at) as year'), DB::raw('MONTH(services.created_at) as month'), DB::raw("ROUND(SUM(TIMESTAMPDIFF(SECOND, services.created_at, client_history.created_at))/COUNT(*)/(3600*24)) as count"))
                    ->where('client_history.service_state_id', $widget->service_state_id)
                    ->groupBy(DB::raw('YEAR(services.created_at), MONTH(services.created_at)'))
                    ->join('services', 'services.id', '=', 'client_history.service_id')
                    ->get();

                $series = self::construct_chart_with_months($services->toArray(), true, 0);

                $categories = trans('template.months');

                $x_title = trans('dashboard.service_created_date');
                $y_title = trans('dashboard.avg_days_count');
                break;
            case 4: // avg price
                $service_state_order = ServiceState::find($widget->service_state_id)->select('order')->get();
                $services = Service::where('estimate_summ', '>', 0)->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('SUM(estimate_summ)/COUNT(*) as count'))
                    ->whereIn('service_state_id', ServiceState::where('order', '>=', $service_state_order)->select('id')->get())
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
                    ->get();

                $series = self::construct_chart_with_months($services->toArray(), true);

                $additional_data['avg_year_price'] = array();
                foreach($series as $year){
                    $avg = 0;
                    foreach($year['data'] as $month){
                        $avg += $month;
                    }
                    $avg = $avg/12;
                    $additional_data['avg_year_price'][strval($year['name'])] = round($avg, 2);
                }

                $categories = trans('template.months');

                $x_title = trans('dashboard.service_created_date');
                $y_title = trans('dashboard.Price_in').' '.$currency_symbol;
                break;
            case 5: // services with state count
                $services = ClientHistory::where('type_id', 2)->where('service_state_id', $widget->service_state_id)
                    ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
                    ->get();

                $series = self::construct_chart_with_months($services->toArray(), true);

                $categories = trans('template.months');

                $x_title = trans('dashboard.service_status_changed_date');
                $y_title = trans('dashboard.quantity');
                break;
            case 6: // services with state count sum
                $services = ClientHistory::where('client_history.type_id', 2)->where('client_history.service_state_id', $widget->service_state_id)
                    ->select(DB::raw('YEAR(client_history.created_at) as year'), DB::raw('MONTH(client_history.created_at) as month'), DB::raw('SUM(services.estimate_summ) as count'))
                    ->groupBy(DB::raw('YEAR(client_history.created_at), MONTH(client_history.created_at)'))
                    ->join('services', 'services.id', '=', 'client_history.service_id')
                    ->get();

                $series = self::construct_chart_with_months($services->toArray(), true);

                $categories = trans('template.months');

                $x_title = trans('dashboard.service_status_changed_date');
                $y_title = trans('dashboard.Price_in').' '.$currency_symbol;
                break;
            case 7: // TABLE status duration
                $services = Service::where('service_state_id', $widget->service_state_id)
                    ->select('master_estimate_id', 'estimate_number', 'id', 'created_at', 'client_contact_id');

                if (array_key_exists('use_range', $post) && $post['use_range']) {
                    $services = $services->whereBetween('created_at', $range);
                }
                $services = $services->get()->toArray();

                foreach($services as $key => $service) {
                    if ($service['master_estimate_id'] != null) {
                        $estimate = Estimate::with('service')->find($service['master_estimate_id']);
                        if ($estimate) {
                            $services[$key]['estimate_number'] = $estimate->get_estimate_number();
                        }
                    }
                    $client_history = ClientHistory::where('service_id', $service['id'])->orderBy('created_at', 'DESC')->first();
                    $service_state_date = $client_history ? $client_history->created_at : $service['created_at'];
                    $services[$key]['interval'] = round((time() - strtotime($service_state_date))/(3600 * 24));
                    $event = Event::with('user')->where('event_type_id', $widget->event_type_id)->where('service_id', $service['id'])->first();
                    if ($event) {
                        $services[$key]['responsible'] = $event->user->name;
                    }else{
                        $services[$key]['responsible'] = self::UNDEFINED;
                    }
                }
                usort($services, function ($item1, $item2) {
                    return $item1['interval'] <=> $item2['interval'];
                });
                $series = $services;
                break;
            case 8: // companies referrer
                $clients = ClientReferrer::select('client_referrers.title as name', DB::raw('COUNT(client_referrer_id) as y'))
                    ->leftJoin('clients', function($join) use ($range, $post) {
                        $join->on('clients.client_referrer_id', '=', 'client_referrers.id');
                        if (array_key_exists('use_range', $post) && $post['use_range']){
                            $join->whereBetween('clients.created_at', $range);
                        }
                    })
                    ->groupBy('client_referrers.id')
                    ->orderBy('y', 'DESC')
                    ->get();

                $series = [[
                    'name' => trans('dashboard.companies_referrers'),
                    'data' => $clients
                ]];

                $total_count = 0;
                foreach($clients as $service){
                    $total_count += $service->y;
                }
                $additional_data['total_count'] = $total_count;

                $categories = array_map(function($s){
                    return $s['name'];
                }, $clients->toArray());

                $x_title = trans('dashboard.referrer');
                $y_title = trans('dashboard.quantity');
                break;
            case 9:
                $data_obj = json_decode($widget->data);
                if ($data_obj->reject_state_id){
                    $actions = ClientHistory::where('service_state_id', '=', $data_obj->reject_state_id);
                    if (array_key_exists('use_range', $post) && $post['use_range']){
                        $actions->whereBetween('created_at', $range);
                    }
                    $rejected_count = $actions->count();

                    $client_history_rejected = ClientHistory::select('client_history.created_at as to_date', 'services.created_at as from_date')->
                        where('client_history.service_state_id', '=', $data_obj->reject_state_id)->
                        join('services', 'services.id', '=', 'client_history.service_id');
                    if (array_key_exists('use_range', $post) && $post['use_range']){
                        $client_history_rejected->whereBetween('client_history.created_at', $range);
                    }
                    $client_history_rejected = $client_history_rejected->get();

                    $rejected_service_time_diffs = 0;
                    foreach($client_history_rejected as $chs){
                        $rejected_service_time_diffs += Carbon::parse($chs->to_date)->diffInDays($chs->from_date); 
                    }
                    if ($client_history_rejected->count() > 0){
                        $time_to_reject = $rejected_service_time_diffs / $client_history_rejected->count();
                    }                   
                }
                if ($data_obj->funnel_values){

                    $started_services = Service::select('estimate_summ');
                    if (array_key_exists('use_range', $post) && $post['use_range']){
                        $started_services->whereBetween('created_at', $range);
                    }
                    $started_services = $started_services->get();
                    $first_item_sum = 0;
                    foreach($started_services as $ss){
                        $first_item_sum += $ss->estimate_summ;
                    }
                    array_push($funnel_items, array(
                        'service_state_id' => 0,
                        'service_state_name' => 'Initial_layer',
                        'count' => $started_services->count(),
                        'sum' => $first_item_sum,
                    ));

                    foreach($data_obj->funnel_values as $service_state){
                        $sum = 0;
                        $service_ids = ClientHistory::where('service_state_id', '=', $service_state->id);
                        if (array_key_exists('use_range', $post) && $post['use_range']){
                            $service_ids->whereBetween('created_at', $range);
                        }
                        $count = $service_ids->distinct('service_id')->count();
                        $service_ids = $service_ids->distinct('service_id')->pluck('service_id');

                        $tmp_services = Service::select('estimate_summ')->whereIn('id', $service_ids)->get();
                        foreach($tmp_services as $tmp_service){
                            $sum += $tmp_service->estimate_summ;
                        }
                        array_push($funnel_items, array(
                            'service_state_id' => $service_state->id,
                            'service_state_name' => $service_state->name,
                            'count' => $count,
                            'sum' => $sum,
                        ));
                    }

                    $last_state_id = end($data_obj->funnel_values)->id;
                    $client_history_sold = ClientHistory::select('client_history.created_at as to_date', 'services.created_at as from_date')->
                        where('client_history.service_state_id', '=', $last_state_id)->
                        join('services', 'services.id', '=', 'client_history.service_id');
                    if (array_key_exists('use_range', $post) && $post['use_range']){
                        $client_history_sold->whereBetween('client_history.created_at', $range);
                    }
                    $client_history_sold = $client_history_sold->get();

                    $sold_service_time_diffs = 0;
                    foreach($client_history_sold as $chs){
                        $sold_service_time_diffs += Carbon::parse($chs->to_date)->diffInDays($chs->from_date); 
                    }

                    if ($client_history_sold->count() > 0){
                        $time_to_sold = $sold_service_time_diffs / $client_history_sold->count();
                    }                    
                }

                break;
            case 10:
                $data_obj = json_decode($widget->data);

                if ($data_obj->initial_state_id && $data_obj->sale_state_id && $data_obj->selected_columns){
                    $cities = [];
                    $services = Service::with(['client_history', 'estimates'])->where('address', '!=', '');

                    if (array_key_exists('use_range', $post) && $post['use_range']){
                        $services->whereBetween('created_at', $range);
                    }
                    else{
                        $services->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()]);
                    }

                    $services = $services->get();

                    $addresses = [];
                    foreach($services as $service){
                        if ($service->address != null && $service->address != ''){
                            array_push($addresses, $service->address);
                        }
                    }
                    $addresses = array_unique($addresses);

                    foreach($addresses as $addr){

                        $addr = str_replace(', Portugal', '', $addr);
                        $addr = str_replace('. Portugal', '', $addr);
                        $addr = str_replace('Portugal', '', $addr);

                        $addr = str_replace(', Portuga', '', $addr);
                        $addr = str_replace('. Portuga', '', $addr);
                        $addr = str_replace('Portuga', '', $addr);

                        $splitted = explode(',', $addr);
                        if (count($splitted) > 0){
                            $city = end($splitted);

                            if ($city != null && $city != '' && preg_match('~[0-9]+~', $city) == false){
                                $city = str_replace('.', '', $city);
                                $city = str_replace('(', '', $city);
                                $city = str_replace(')', '', $city);
                                array_push($cities, ucfirst(trim($city)));
                                continue;
                            }

                            $splitted_space = explode(' ', $city);
                            if (count($splitted_space) > 0){
                                $cityIn = end($splitted_space);
    
                                if ($cityIn != null && $cityIn != '' && preg_match('~[0-9]+~', $cityIn) == false){
                                    $city = str_replace('.', '', $cityIn);
                                    $city = str_replace('(', '', $cityIn);
                                    $city = str_replace(')', '', $cityIn);
                                    array_push($cities, ucfirst(trim($cityIn)));
                                }                                
                            }   
                        }                        
                    }

                    $cities = array_unique($cities);
                    
                    // $cities_result = [];

                    foreach($cities as $city){
                        if ($city == null || $city == ''){
                            continue;
                        }
                        $object = array(
                            "City"=>$city,
                            "Total_services"=>0, 
                            "Total_services_in_sale_status"=>0, 
                            "Total_services_in_sale_status_percent"=>0.0,
                            "Average_decision_time"=>0.0,
                            "Average_sum"=>0.0,
                            "Total_sum"=>0.0,
                            "Average_margin"=>0.0,
                            "Number_of_second_buys"=>0.0,
                        );
                        
                        $times = [];
                        $sums = [];
                        $margins = [];
                        foreach($services as $service){
                            if (strpos(strtolower($service->address), strtolower($city)) !== false){
                                $object['Total_services'] += 1;
                                
                                $client_histories = $service->client_history()->get();
                                foreach($client_histories as $cl){
                                    if ($cl->service_state_id == $data_obj->sale_state_id){
                                        
                                        $object['Total_services_in_sale_status'] += 1;

                                        array_push($service_addresses, 
                                        array(
                                            "address"=> $service->address,
                                            "service_id"=>$service->id, 
                                            "client_contact_id"=>$service->client_contact_id, 
                                        ));

                                        array_push($sums, $service->estimate_summ);
                                        $days = Carbon::parse($cl->created_at)->diffInDays(Carbon::parse($service->created_at));
                                        array_push($times, $days);
                                        if ($service->master_estimate_id != null){
                                            foreach($service->estimates() as $est){
                                                if ($est->id == $service->master_estimate_id){
                                                    if ($est->additional_price != null && $est->additional_price > 0){
                                                        array_push($margins, $service->estimate_summ * ($est->additional_price / 100));
                                                    }
                                                }
                                            }
                                        }

                                        $previous_buys = ClientHistory::where('client_contact_id', $cl->client_contact_id)->where('service_state_id', $data_obj->sale_state_id)->where('service_id', '!=', $cl->service_id)->count();
                                        if ($previous_buys > 0){
                                            $object['Number_of_second_buys'] += 1;
                                        }

                                    break;
                                    }
                                }      
                            }
                        }

                        if ($object['Total_services'] != 0){
                            $object['Total_services_in_sale_status_percent'] = round($object['Total_services_in_sale_status'] / $object['Total_services'] * 100, 2);
                        }

                        if (count($times) > 0){
                            $object['Average_decision_time'] = round(array_sum($times)/count($times), 2);
                        }
                        if (count($sums) > 0){
                            $object['Average_sum'] = round(array_sum($sums)/count($sums), 2);
                        }

                        $object['Total_sum'] = round(array_sum($sums), 2);

                        if (count($margins) > 0){
                            $object['Average_margin'] = round(array_sum($margins)/count($margins), 2);
                        }

                        array_push($cities_result, $object);
                    }
                }                

                break;
        }

        return array(
            'id' => $post['id'],
            'series' => $series,
            'additional_data' => $additional_data,
            'categories' => $categories,
            'x_title' => $x_title,
            'y_title' => $y_title,
            'funnel_items' => $funnel_items,
            'rejected_count' => $rejected_count,
            'time_to_reject' => $time_to_reject,
            'time_to_sold' => $time_to_sold,
            'cities' => $cities_result,
            'service_addresses' => $service_addresses,
        );
    }

    public function build_widget_more(){
        $post = $this->request->all();
        $widget = DashboardWidget::find($post['id']);

        $year = $post['year'];
        $month = $post['month'];
        $start = DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-01 00:00:00");
        $end = DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-01 00:00:00");
        $end->add(new DateInterval('P1M'));
        $range = array($start, $end);

        $services = ClientHistory::where('type_id', 2)->where('client_history.service_state_id', $widget->service_state_id)
            ->select('services.master_estimate_id', 'services.estimate_number', 'services.estimate_summ', 'services.client_contact_id')
            ->whereBetween('client_history.created_at', $range)
            ->join('services', 'services.id', '=', 'client_history.service_id')
            ->get();

        foreach($services as $key => $service) {
            if ($service['master_estimate_id'] != null) {
                $estimate = Estimate::with('service')->find($service['master_estimate_id']);
                if ($estimate) {
                    $services[$key]['estimate_number'] = $estimate->get_estimate_number();
                }
            }
        }

        return $services;
    }

    /*
     * Function converts [['year' => 2015, 'month' => 1, 'count' => 4],
     *                    ['year' => 2015, 'month' => 5, 'count' => 7],
     *                    ['year' => 2016, 'month' => 3, 'count' => 9]]
     * To [['name' => '2015', 'data' => [4, 0, 0, 0, 7, 0, 0, 0, 0, 0, 0, 0]],
     *     ['name' => '2016', 'data' => [0, 0, 9, 0, 0, 0, 0, 0, 0, 0, 0, 0]]]
     * */
    private function construct_chart_with_months($input, $with_round, $precision = 2){
        $years_grouped = array();
        foreach($input as $key => $item){
            $years_grouped[$item['year']][$item['month']] = $with_round ? round($item['count'], $precision) : $item['count'];
        }

        foreach($years_grouped as $key => $item){
            $months = array();
            for($month = 1; $month <= 12; $month++){
                $months[$month] = isset($item[$month]) ? $item[$month] : 0;
            }
            $years_grouped[$key] = $months;
        }

        $data = array();
        foreach($years_grouped as $key => $item){
            $chart = array();
            $chart['name'] = strval($key);
            $chart['data'] = array_values($item);
            array_push($data, $chart);
        }
        return $data;
    }

}
