<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\UserRole;
use App\User;
use Log;
use Exception;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Project\Models\Equipment;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\Manufacturer;
use Rkesa\Client\Models\Client;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\ProjectType;
use Rkesa\Project\Models\Specification;
use Rkesa\Project\Models\SpecificationEquipment;

class SpecificationController extends Controller
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
            $specifications = Specification::with('equipment.manufacturer')->select($fields_array)->withCount('equipment');
//                    switch($user->cando('clients', 'read')){
//                        case 0:
//                            $clients->where('id', null);
//                            break;
//                        case 1:
//                            $event_clients = Event::where('user_id', $user->id)->pluck('client_contact_id')->all();
//                            $services_clients = Service::where('responsible_user_id', $user->id)->pluck('client_contact_id')->all();
//                            $contact_ids = array_unique(array_merge($event_clients, $services_clients, [1,2]));
//                            $client_ids = ClientContact::whereIn('id', $contact_ids)->pluck('client_id')->all();
//                            $clients->whereIn('id', $client_ids);
//                            break;
//                        case 2:
//                            $event_clients = Event::whereIn('user_id', $user->groupmates_ids())->pluck('client_contact_id')->all();
//                            $services_clients = Service::whereIn('responsible_user_id', $user->groupmates_ids())->pluck('client_contact_id')->all();
//                            $contact_ids = array_unique(array_merge($event_clients, $services_clients, [1,2]));
//                            $client_ids = ClientContact::whereIn('id', $contact_ids)->pluck('client_id')->all();
//                            $clients->whereIn('id', $client_ids);
//                            break;
//                        case 3:
//                            break;
//                    }
            $search = $request->input('search', '');
            if ($search != ''){
                $specifications->where('name', 'like', '%'.$search.'%');
//                        // parentheses in condition
//                        $projects->where(function($c) use ($query) {
//                            $c->where('name', 'like', '%'.$query.'%')
//                                ->orWhere('email', 'like', '%'.$query.'%')
//                                ->orWhere('id', 'like', '%'.$query.'%')
//                                ->orWhere('phone', 'like', '%'.$query.'%')
//                                ->orWhere('client_group', 'like', '%'.$query.'%');
//                        });
            }

            $specifications->orderBy($sort, $order);

            $res->total = $specifications->count();
            $res->rows = $specifications->offset($offset)->limit($limit)->get();
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
            $spec = new Specification;
            $spec->fill($request->all());
            $spec->save();
            foreach($request['equipment'] as $eq){
                $equipment = new SpecificationEquipment;
                $equipment->fill($eq);
                $equipment->specification_id = $spec->id;
                $equipment->save();
            }
            $res->id = $spec->id;
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
            $spec = Specification::find($id);
            $spec->fill($request->all());
            $spec->save();
            foreach($request['equipment'] as $eq){
                if ($eq['id'] == 0) {
                    $equipment = new SpecificationEquipment;
                    $equipment->fill($eq);
                    $equipment->specification_id = $spec->id;
                    $equipment->save();
                }else {
                    $equipment = SpecificationEquipment::find($eq['id']);
                    $equipment->fill($eq);
                    $equipment->save();
                }
            }
            foreach($request['removed_equipment'] as $re){
                if ($re != 0) {
                    SpecificationEquipment::destroy($re);
                }
            }
            $res->id = $spec->id;
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
        $specification = Specification::with('equipment.manufacturer')->find($id);

        return $specification;
    }

    public function delete()
    {
        //
    }
}
