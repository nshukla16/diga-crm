<?php

namespace Rkesa\Hr\Http\Controllers;

use App\Events\GroupsChanged;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use Log;
use Exception;
use App\Group;

class GroupsController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $fields = $request->input('fields', '*');
            $fields_array = explode(",", $fields);

            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'id');
            if ($sort == '') {
                $sort = 'id';
            }
            $order = $request->input('order', 'asc');
            if ($order == '') {
                $order = 'asc';
            }

            $groups = Group::with(['client'])->select($fields_array);

            $groups->orderBy($sort, $order);

            $res->total = $groups->count();
            $res->rows = $groups->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $group = new Group;

            $group->fill($request->all());

            $group->save();
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
