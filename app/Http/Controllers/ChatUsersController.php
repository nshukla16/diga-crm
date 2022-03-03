<?php

namespace App\Http\Controllers;

use Log;
use Auth;
use App\Chat;
use App\User;
use Exception;
use App\ChatMember;
use Illuminate\Http\Request;
use App\Events\ChatCreatedEvent;
use Rkesa\Service\Models\ServiceReferrer;

class ChatUsersController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'photo')->get();
        $service_referrers = ServiceReferrer::select('id', 'name')->get();
        foreach($service_referrers as $ccr){
            $users->push($ccr);
        }           

        return $users;
    }
}
