<?php

namespace Rkesa\Service\Http\Controllers;

use App\Events\ServiceSettingsChanged;
use App\GlobalSettings;
use App\Group;
use App\Http\Controllers\Controller;
use Rkesa\Calendar\Models\Checklist;
use Rkesa\Calendar\Models\EventType;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Dashboard\Models\Dashboard;
use Rkesa\Dashboard\Models\DashboardEntity;
use Rkesa\Dashboard\Models\DashboardEntityField;
use Rkesa\Dashboard\Models\DashboardWidget;
use Rkesa\Service\Models\Service;
use Rkesa\Service\Models\ServiceScope;
use Rkesa\Service\Models\ServiceState;
use Illuminate\Http\Request;
use Exception;
use Log;
use App\User;
use Rkesa\Service\Models\ServiceStateAction;
use Rkesa\Service\Models\ServiceType;

class ServiceSettingsController extends Controller
{

    public function save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            foreach($request['states_removed'] as $i) {
                $gs = GlobalSettings::first();
                if ($gs->new_service_state_id == $i){
                    $ss = ServiceState::find($i);
                    throw new Exception(trans('service.Impossible_to_remove_status_incoming_requests', ['status' => $ss->name]));
                }
            }

            foreach($request['states_removed'] as $i) {
                $services_exist = Service::where('service_state_id', $i)->count() > 0;
                if ($services_exist){
                    $ss = ServiceState::find($i);
                    throw new Exception(trans('service.Impossible_to_remove_status', ['status' => $ss->name]));
                }
            }

            foreach($request['states'] as $i) {
                if ($i['type'] == 1 && $i['destination_state_id'] == 0){
                    throw new Exception(trans('service.Impossible_to_not_have_destination', ['status' => $i['name']]));
                }
            }

            // Service states
            foreach($request['actions_removed'] as $i){
                $action_to_delete = ServiceStateAction::find($i);
                if ($action_to_delete) {
                    $old_filepath = public_path() . $action_to_delete->email_file;
                    if ($action_to_delete->email_file != null && file_exists($old_filepath)) {
                        unlink($old_filepath);
                    }
                    $action_to_delete->delete();
                }
            }
            foreach($request['states'] as $state){
                if ($state['id'] == 0){
                    $service_state = new ServiceState;
                }else{
                    $service_state = ServiceState::find($state['id']);
                }
                $service_state->fill($state);
                $service_state->save();
                if ($state['id'] == 0){
                    $dashboards = Dashboard::all();
                    foreach ($dashboards as $dashboard){
                        $dashboard_entity = new DashboardEntity;
                        $dashboard_entity->hide = false;
                        $dashboard_entity->service_state_id = $service_state->id;
                        $dashboard_entity->dashboard_id = $dashboard->id;
                        $dashboard_entity->save();
                        $dashboard_entity_field = new DashboardEntityField;
                        $dashboard_entity_field->type = 1;
                        $dashboard_entity_field->dashboard_entity_id = $dashboard_entity->id;
                        $dashboard_entity_field->save();
                    }
                }
                $state['id'] = $service_state->id;
                foreach($state['service_state_actions'] as $action){
                    if ($action['id'] == 0){
                        $service_state_action = new ServiceStateAction;
                    }else{
                        $service_state_action = ServiceStateAction::find($action['id']);
                    }
                    $service_state_action->fill($action);
                    if (strpos($action['email_file'], '/img/uploads/temp/') !== false) {
                        // Remove old
                        if ($service_state_action->email_file != null){
                            $old_filepath = public_path() . $service_state_action->email_file;
                            if (file_exists($old_filepath)) {
                                unlink($old_filepath);
                            }
                        }
                        // Copy new
                        $old_file = substr($action['email_file'], 1);
                        $filename = pathinfo($action['email_file'], PATHINFO_BASENAME);
                        $new_file = 'img/uploads/'.$filename;
                        rename($old_file, $new_file);
                        $service_state_action->email_file = '/'.$new_file;
                    }
                    $service_state_action->service_state_id = $service_state->id;
                    $service_state_action->save();
                }
            }
            $first_of_undelete = $request['states'][0]['id'];
            foreach($request['states_removed'] as $i){
                $r = ServiceState::find($i);
                if ($r){
                    $actions_to_delete = ServiceStateAction::where('service_state_id', $r->id);
                    foreach($actions_to_delete->get() as $action) {
                        $old_filepath = public_path() . $action->email_file;
                        if ($action->email_file != null && file_exists($old_filepath)) {
                            unlink($old_filepath);
                        }
                    }
                    $gs = GlobalSettings::first();
                    if ($gs->new_service_state_id == $r->id){
                        $gs->new_service_state_id = $first_of_undelete;
                    }
                    if ($gs->add_service_state_id == $r->id){
                        $gs->add_service_state_id = $first_of_undelete;
                    }
                    $gs->save();
                    $actions_to_delete->delete();
                    $dashboard_widgets = DashboardWidget::where('service_state_id', $i)->get();
                    foreach($dashboard_widgets as $widget){
                        $widget->service_state_id = $first_of_undelete;
                        $widget->save();
                    }
                    $r->dashboard_entities()->delete();
                    $r->delete();
                }
            }
            // Service types
            foreach($request['types_removed'] as $i){
                $r = ServiceType::find($i);
                if ($r){
                    $services = Service::where('service_type_id', $r->id)->get();
                    foreach($services as $service){
                        $service->service_type_id = null;
                        $service->save();
                    }
                    $r->delete();
                }
            }
            foreach($request['types'] as $i){
                if ($i['id'] == 0){
                    $r = new ServiceType;
                    $r->name = $i['name'];
                    $r->price = $i['price'];
                    $r->save();
                }else {
                    $r = ServiceType::find($i['id']);
                    $r->name = $i['name'];
                    $r->price = $i['price'];
                    $r->save();
                }
            }
            // Service scopes
            foreach($request['my_scopes'] as $i){
                if ($i['id'] == 0){
                    $r = new ServiceScope;
                    $r->name = $i['name'];
                    $r->start_service_state_id = $i['start_service_state_id'];
                    $r->end_service_state_id = $i['end_service_state_id'];
                    $r->save();
                }else {
                    $r = ServiceScope::find($i['id']);
                    $r->name = $i['name'];
                    $r->start_service_state_id = $i['start_service_state_id'];
                    $r->end_service_state_id = $i['end_service_state_id'];
                    $r->save();
                }
            }
            foreach($request['scopes_removed'] as $i){
                $r = ServiceScope::find($i);
                if ($r){
                    $new_ss = ServiceScope::where('id', '!=', $i)->first();
                    Group::where('service_scope_id', $i)->update(['service_scope_id' => $new_ss->id]);
                    $r->delete();
                }
            }
            $gs = GlobalSettings::first();
            $gs->fill($request['global_settings']);
            $gs->save();
            broadcast(new ServiceSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
