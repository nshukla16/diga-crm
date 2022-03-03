<?php

namespace Rkesa\Estimate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\Estimate\Models\EstimateUnit;

class EstimateUnitsController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return EstimateUnit::select($fields_array)->get();
    }
}
