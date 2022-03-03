<?php

namespace Rkesa\Service\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Service\Models\Service;
use Carbon\Carbon;

class ServiceStatesController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return ServiceState::withTrashed()->with('service_state_actions')->select($fields_array)->orderBy('order')->get();
    }

    public function services(Request $request)
    {
        $from = $request->input('from', Carbon::now()->subYear());
        $to = $request->input('to', Carbon::now());

        $result = [];
        $service_states = ServiceState::orderBy('order')->get();
        foreach($service_states as $ss)
        {
            $result[$ss->name] = [];
            $services = Service::where('service_state_id', $ss->id)->whereBetween('created_at', [$from, $to])->get();
            $result[$ss->name]['services'] = $services;
        }

        return response()->json($result, 200, [], JSON_NUMERIC_CHECK);
    }
}
