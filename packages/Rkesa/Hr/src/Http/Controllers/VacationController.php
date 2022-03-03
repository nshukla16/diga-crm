<?php

namespace Rkesa\Hr\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Role;
use App\User;
use App\Vacation;
use Exception;
use Illuminate\Support\Facades\Log as FacadesLog;
use Log;

class VacationController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->input('start', null);
        $end = $request->input('end', null);
        $user_id = $request->input('user_id', 0);
        $approved = $request->input('approved', null);

        $res = (object)array();
        $res->errcode = 0;
        try {
            $vacations = Vacation::with('user');

            if (isset($start, $end)){
                $vacations->whereBetween('start', array($start, $end));
            }
            if ($approved == 1){
                $vacations->where('is_approved', false);
            }
            if ($approved == 2){
                $vacations->where('is_approved', true);
            }
            if ($user_id != 0) {
                $vacations->where('user_id', $user_id);
            }

            $res->rows = $vacations->get();

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
        try{
            $vacation = new Vacation;

            $vacation->fill($request->all());

            $vacation->save();
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
        try{
            $vacation = Vacation::find($id);

            $vacation->fill($request->all());

            $vacation->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function delete(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $vacation = Vacation::find($id)->delete();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
