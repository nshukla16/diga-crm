<?php

namespace App\Http\Controllers;

use App\Group;
use App\User;
use Log;
use Exception;
use Illuminate\Http\Request;
use Rkesa\Service\Models\ServiceScope;

class UserSettingsController extends Controller
{
    public function groups(Request $request)
    {
//        $search = $request->input('search', false);
//        if ($search !== false){
//            $heads = $heads->filter(function ($value, $key) use ($search) {
//                return stripos($value->name, $search) !== false;
//            });
//            $heads = array_values($heads->toArray());
//            return response()->json($heads);
//        }
    }
}
