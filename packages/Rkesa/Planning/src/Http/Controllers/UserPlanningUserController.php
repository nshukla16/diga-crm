<?php

namespace Rkesa\Planning\Http\Controllers;

use App\GlobalSettings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Rkesa\Planning\Models\UserPlanning;
use Rkesa\Planning\Models\UserPlanningUser;
use Rkesa\Planning\Models\UserPlanningUserTask;


class UserPlanningUserController extends Controller
{
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = new UserPlanningUser;
            $user->fill($request->all());
            $user->save();
            $res->id = $user->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function update(Request $request, $id) {

        $res = (object)array();
        $res->errcode = 0;
        try {
            $task = UserPlanningUser::find($id);
            $task->fill($request->all());
            $task->save();
        }
        catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function destroy(Request $request, $id)
    {
        $upu = UserPlanningUser::findOrFail($id);

        $upu->tasks()->delete();

        $upu->delete();
    }


}