<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\GroupsChanged;
use App\Group;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;

class GroupsController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return Group::select($fields_array)->get();
    }

    public function update(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            foreach($request['removed_groups'] as $i){
                $r = Group::find($i);
                if ($r){
                    $r->delete();
                }
            }
            foreach($request['my_groups'] as $i){
                switch($i['id']){
                    case -1:
                        $group_id = Group::create(['name' => $i['name'], 'service_scope_id' => $i['service_scope_id'], 'head_user_id' => $i['head_user_id']])->id;
                        break;
                    case 0:
                        $group_id = 0;
                        break;
                    default:
                        $r = Group::find($i['id']);
                        $r->name = $i['name'];
                        $r->service_scope_id = $i['service_scope_id'];
                        $r->type = $i['type'];
                        $r->head_user_id = array_key_exists('head_user_id', $i) ? $i['head_user_id'] : null;
                        $r->save();
                        $group_id = $i['id'];
                }
                User::whereIn('id', $i['users_ids'])->update(['group_id' => $group_id]);
            }
            broadcast(new GroupsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
