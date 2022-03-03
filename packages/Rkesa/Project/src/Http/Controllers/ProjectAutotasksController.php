<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Exception;
use Log;
use Rkesa\Project\Models\ProjectAutotask;

class ProjectAutotasksController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $res->autotasks = ProjectAutotask::with('recipients')->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
