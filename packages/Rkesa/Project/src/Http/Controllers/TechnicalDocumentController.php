<?php

namespace Rkesa\Project\Http\Controllers;

use Log;
use App\User;
use Exception;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Client\Models\Client;
use Rkesa\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\ProjectType;
use Rkesa\Project\Models\TechnicalDocument;

class TechnicalDocumentController extends Controller
{
    public function index(Request $request)
    {
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
            $tds = TechnicalDocument::with(['manufacturer', 'project'])->select($fields_array);

            $tds->orderBy($sort, $order);

            $res->total = $tds->count();
            $res->rows = $tds->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function destroy($id)
    {
        $td = TechnicalDocument::findOrFail($id);
        $td->delete();
    }
}
