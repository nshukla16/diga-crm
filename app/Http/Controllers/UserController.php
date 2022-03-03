<?php

namespace App\Http\Controllers;

use App\Events\ProfileChanged;
use App\Role;
use App\User;
use Exception;
use Log;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function permissions_save(Request $request, $user_id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = User::find($user_id);
            if (User::where('is_admin', true)->count() == 1 && $user->is_admin && !boolval($request['edit_user']['is_admin'])) {
                throw new Exception(trans('template.There_must_be_at_least_one_administrator'));
            } else {
                $user->is_admin = boolval($request['edit_user']['is_admin']);
                $user->save();
            }
            $user->clear_roles_cache();
            $changed_roles = $request['edit_user']['roles'];


            foreach ($changed_roles as $changed_role) {
                $role = Role::where('user_id', $user_id)->where('action', $changed_role['action'])->first();
                if ($role != null) {
                } else {
                    $role = new Role;
                    $role->action = $changed_role['action'];
                    $role->user_id = $user_id;
                }
                $role->create = $changed_role['create'];
                $role->read = $changed_role['read'];
                $role->update = $changed_role['update'];
                $role->delete = $changed_role['delete'];
                $role->addit = $changed_role['addit'];
                $role->save();
            }

            $user->new_client_notifications = boolval($request['edit_user']['new_client_notifications']);
            $user->new_fb_messages_notifications = boolval($request['edit_user']['new_fb_messages_notifications']);
            $user->can_see_prices = boolval($request['edit_user']['can_see_prices']);
            $user->can_see_financial_calendar = boolval($request['edit_user']['can_see_financial_calendar']);
            $user->email_suggestions = boolval($request['edit_user']['email_suggestions']);
            $user->can_use_timetracker = boolval($request['edit_user']['can_use_timetracker']);
            $user->can_view_results_of_timetracker = boolval($request['edit_user']['can_view_results_of_timetracker']);
            $user->dashboard_id = $request['edit_user']['dashboard_id'];
            $user->can_view_calls = $request['edit_user']['can_view_calls'];
            $user->missed_calls_notifications = $request['edit_user']['missed_calls_notifications'];
            $user->can_see_kpi = $request['edit_user']['can_see_kpi'];
            $user->can_export = $request['edit_user']['can_export'];
            $user->can_give_discount = $request['edit_user']['can_give_discount'];
            $user->can_enter_timesheet_and_consumption = $request['edit_user']['can_enter_timesheet_and_consumption'];
            $user->can_approve_vacations = $request['edit_user']['can_approve_vacations'];
            $user->can_finish_projects = $request['edit_user']['can_finish_projects'];
            $user->save();
            broadcast(new ProfileChanged($user));
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function save_widgets_order(Request $request, $user_id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = User::find($user_id);
            $user->widget_order = json_encode($request['dashboard_widget_ids']);
            $user->save();
            // broadcast(new ProfileChanged($user));
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
