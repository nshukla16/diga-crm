<?php

namespace Rkesa\CalendarExtended\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\CalendarExtended\Models\EventGroup;

class EventGroupsController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return EventGroup::select($fields_array)->get();
    }
}
