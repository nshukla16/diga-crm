<?php

namespace Rkesa\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User as User;
use Illuminate\Http\Request;
use Exception;
use Log;
use Auth;
use DB;

class ContactAccessController extends Controller
{
    public function page_of_contact($token)
    {
//        $contact = ClientContact::where('access_token', $token)->first();
        
        // return view('client::contact', ['token' => $token]);
        return view('client::telegram', ['token' => $token]);
    }


}
