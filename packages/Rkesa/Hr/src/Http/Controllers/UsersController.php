<?php

namespace Rkesa\Hr\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use Log;
use Exception;

class UsersController extends Controller
{

    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return User::withTrashed()->select($fields_array)->get();
    }
}
