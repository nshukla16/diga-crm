<?php

namespace Rkesa\Service\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Rkesa\Service\Models\ServiceScope;

class ServiceScopesController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return ServiceScope::select($fields_array)->get();
    }
}
