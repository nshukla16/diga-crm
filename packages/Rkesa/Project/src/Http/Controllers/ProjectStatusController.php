<?php

namespace Rkesa\Project\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\ProjectStatus;
use Rkesa\Project\Models\ProjectType;

class ProjectStatusController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return ProjectStatus::select($fields_array)->get();
    }
}
