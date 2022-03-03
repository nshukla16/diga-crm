<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\LegalEntityContract;

class LegalEntityContractController extends Controller
{
    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $le = new LegalEntityContract;
            $le->fill($request->all());
            $le->save();

            $res->id = $le->id;
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
        try {
            $le = LegalEntityContract::find($id);
            $le->fill($request->all());
            $le->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
