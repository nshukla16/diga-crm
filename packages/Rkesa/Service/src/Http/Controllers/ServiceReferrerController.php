<?php

namespace Rkesa\Service\Http\Controllers;

use DB;
use Log;
use Auth;
use App\Chat;
use Exception;
use App\ChatMember;
use App\User as User;
use App\GlobalSettings;
use Illuminate\Support\Str;
use App\Events\UsersChanged;
use Illuminate\Http\Request;
use App\Http\Traits\MailTrait;
use Rkesa\Service\Models\Service;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ContactAssigned;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Service\Models\ServiceReferrer;
use App\Http\Helpers\TelegramMadelineProto;
use App\Http\Controllers\FacebookController;
use App\Http\Traits\ClientAndContactWithTrait;
use Illuminate\Support\Facades\Log as IlluminateLog;

class ServiceReferrerController extends Controller
{
    public function register(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        $access_token = $request->access_token;
        $name = $request->name;
        $agree_with_promotion = $request->agree_with_mailing;
        $locale = $request->locale;

        $res = (object)array();
        $res->errcode = 0;
        try
        {
            $service = Service::where('access_token', $access_token)->first();

            $existing_referrer = ServiceReferrer::where('email', $email)->where('service_id', $service->id)->first();
            if ($existing_referrer)
            {
                throw Exception('Email is already in use');
            }

            $referrer = new ServiceReferrer;
            $referrer->service_id = $service->id;
            $referrer->email = $email;
            $referrer->password = Hash::make($password);
            $referrer->name = $name;
            $referrer->hash = Str::uuid();
            $referrer->agree_to_receive_promotions = $agree_with_promotion;
            $referrer->locale = $locale;
            $referrer->save();

            $chat = Chat::where('service_token', $access_token)->firstOrFail();
            $member = new ChatMember;
            $member->chat_id = $chat->id;
            $member->service_referrer_id = $referrer->id;
            $member->save();

            broadcast(new UsersChanged());

            $res->hash = $referrer->hash;
            $res->id = $referrer->id;
            $res->email = $referrer->email;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $access_token = $request->input('access_token');

        $res = (object)array();
        $res->errcode = 0;
        try
        {
            $service = Service::where('access_token', $access_token)->firstOrFail();
            $referrer = ServiceReferrer::where('email', $email)->where('service_id', $service->id)->firstOrFail();
            if (Hash::check($password, $referrer->password)){
                $res->hash = $referrer->hash;
                $res->id = $referrer->id;
                $res->email = $referrer->email;
            }
            else
            {
                throw Exception("Password is wrong");
            }

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function check_hash($hash)
    {
        $res = (object)array();
        $res->errcode = 0;
        try
        {
            $referrer = ServiceReferrer::where('hash', $hash)->firstOrFail();

            $res->hash = $referrer->hash;
            $res->id = $referrer->id;
            $res->email = $referrer->email;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function users($access_token)
    {
        $res = (object)array();
        $res->errcode = 0;
        try
        {
            $chat = Chat::with('members')->where('service_token', $access_token)->firstOrFail();

            $users = [];
            
            foreach($chat->members as $member)
            {
                $obj = new \stdClass;

                if ($member->user_id){
                    $obj->id = $member->user->id;
                    $obj->name = $member->user->name;
                    $obj->photo = $member->user->photo;
                }
                if ($member->service_referrer_id){
                    $obj->id = $member->service_referrer->id;
                    $obj->name = $member->service_referrer->name;
                    $obj->photo = '/img/no_profile_picture.png';
                }

                array_push($users, $obj);
            }

            $res->users = $users;

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function join_chat(Request $request)
    {
        $email = $request->email;
        $access_token = $request->access_token;
        $name = $request->name;
        $agree_with_promotion = $request->agree_with_mailing;
        $locale = $request->locale;
        $formatted_cell_phone = $request->formatted_phone;

        $res = (object)array();
        $res->errcode = 0;
        try
        {
            $service = Service::where('access_token', $access_token)->first();

            $referrer = new ServiceReferrer;
            $referrer->service_id = $service->id;
            $referrer->email = $email;
            $referrer->password = Hash::make('password');
            $referrer->name = $name;
            $referrer->hash = Str::uuid();
            $referrer->agree_to_receive_promotions = $agree_with_promotion;
            $referrer->locale = $locale;
            $referrer->formatted_cell_phone = $formatted_cell_phone;            

            $gs = GlobalSettings::first();
            if ($gs->telegram_enabled == true){
                $tg_helper = new TelegramMadelineProto;
                $first_name = "";
                $last_name = "";
                $pieces = explode(" ", $referrer->name);
                if (count($pieces) > 1){
                    $first_name = $pieces[0];
                    $last_name = $pieces[1];
                }
                else{
                    $first_name = $referrer->name;
                    $last_name = $referrer->name; 
                }
                $tg_id = $tg_helper->add_to_contacts(null, $first_name, $last_name, $referrer->formatted_cell_phone);
                
                if ($tg_id > 0){
                    $referrer->tg_id = $tg_id;

                    if ($tg_helper->create_chat($service->estimate_number. ' ' .$service->name, [$referrer->tg_id]) != 'ok'){
                        $res->errcode = 1;
                        $res->errmess = "Adding to chat error";
                    } 
                }
                else {
                    $res->errcode = 1;
                    $res->errmess = "Adding to contacts error";
                }
            }

            $referrer->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
