<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\UserRole;
use App\User;
use Log;
use Exception;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\Manufacturer;
use Rkesa\Client\Models\Client;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\ProjectType;

class ManufacturerController extends Controller
{
    public function index(Request $request)
    {
//        $user = Auth::user();

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
            $manufacturers = Manufacturer::select($fields_array);

            $manufacturers->orderBy($sort, $order);

            $res->total = $manufacturers->count();
            $res->rows = $manufacturers->offset($offset)->limit($limit)->get();
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
            $manufacturer = new Manufacturer;
            $manufacturer->fill($request->all());
            $manufacturer->save();
            $res->id = $manufacturer->id;
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
            $manufacturer = Manufacturer::find($id);
            $manufacturer->fill($request->all());
            $manufacturer->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function show(Request $request, $id)
    {
        return Manufacturer::with(['equipment', 'contacts', 'contracts', 'orders', 'projects_manufacturers', 'projects_manufacturers.project'])->findOrFail($id);
    }

    public function destroy($id)
    {
//        $manufacturer = Auth::user();
        $manufacturer = Manufacturer::findOrFail($id);
//        if (!$user->can_with_client('delete', $client) || $id == 1){ // id == 1 if it is test company
//            return response('', 403);
//        }
//        // Contacts and phones
//        $contact_ids = ClientContact::where('client_id', $client->id)->pluck('id')->toArray();
//        foreach ($contact_ids as $contact_id) {
//            $e = app('Rkesa\Client\Http\Controllers\ContactController')->destroy($request, $contact_id);
//            Log::info($e);
//        }
        $manufacturer->delete();
    }
}
