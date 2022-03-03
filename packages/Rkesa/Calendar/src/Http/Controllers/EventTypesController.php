<?php

namespace Rkesa\Calendar\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\Calendar\Models\EventType;

class EventTypesController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return EventType::select($fields_array)->orderBy('order')->get();
    }
}
