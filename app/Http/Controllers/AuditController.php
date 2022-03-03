<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Log;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Rkesa\Project\Models\Project;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function project(Request $request, $id){
        $user = Auth::user();

        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == ''){ $sort = 'created_at'; }
        $order = $request->input('order', 'asc');
        if ($order == ''){ $order = 'asc'; }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;

        try{
            $audits = Audit::where(DB::raw("FIND_IN_SET('project:".$id."', tags)"), '>', 0)->get();

            $audits_collection = collect($audits);

            if ($order == 'desc'){
                $audits_collection = $audits_collection->sortByDesc($sort);
            }
            else if ($order == 'asc'){
                $audits_collection = $audits_collection->sortBy($sort);
            }

            $res->total = $audits_collection->count();
            $res->rows = array_values($audits_collection->slice($offset, $limit)->toArray());

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
