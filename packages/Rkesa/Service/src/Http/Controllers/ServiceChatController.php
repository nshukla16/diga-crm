<?php

namespace Rkesa\Service\Http\Controllers;

use DB;
use Log;
use Auth;
use App\Chat;
use Exception;
use App\ChatMember;
use App\ChatMessage;
use App\User as User;
use App\GlobalSettings;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\MailTrait;
use Rkesa\Service\Models\Service;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ContactAssigned;
use Rkesa\Client\Models\ClientContact;
use App\Http\Helpers\TelegramMadelineProto;
use App\Http\Controllers\FacebookController;
use App\Http\Traits\ClientAndContactWithTrait;
use Rkesa\Client\Models\ClientContactReferrer;
use Illuminate\Support\Facades\Log as IlluminateLog;

class ServiceChatController extends Controller
{
    public function enable_access($id)
    {
        $user =  Auth::user();
        $res = (object)array();
        $res->errcode = 0;
        try {
            $service = Service::find($id);
            $service->access_enabled = !$service->access_enabled;
            if (!$service->access_token){
                $service->access_token = $this->generate_token($service);
            }
            $service->save();

            // $chat = new Chat;
            // $chat->name = $service->estimate_number. ' ' .$service->name;
            // $chat->type = 5;
            // $chat->service_token = $service->access_token;
            // $chat->save();

            $res->link = $service->access_token;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public function generate_new_link($id)
    {
        $user =  Auth::user();
        $res = (object)array();
        $res->errcode = 0;
        try {
            $service = Service::find($id);
            $service->access_token = $this->generate_token($service);
            $service->save();

            // $chat = new Chat;
            // $chat->name = $service->estimate_number. ' ' .$service->name;
            // $chat->type = 5;
            // $chat->client_contact_token = $service->access_token;
            // $chat->save();

            $res->link = $service->access_token;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    private function generate_token($service)
    {
        return md5($service->name . 'diga' . time());
    }

    public function get($id)
    {
        $user =  Auth::user();
        $res = (object)array();
        $res->errcode = 0;
        try {
            $service = Service::find($id);
            $chat = Chat::with(['members', 'members.user'])->where('service_token', $service->access_token)->first();
            if ($chat){
                $res->members = $chat->members;
            }            
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public function save(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $service = Service::find($id);
            $chat = Chat::with('members')->where('service_token', $service->access_token)->first();
            $gs = GlobalSettings::first();

            if (!$chat)
            {
                $chat = new Chat;
                $chat->name = $service->estimate_number. ' ' .$service->name;
                $chat->type = 5;
                $chat->service_token = $service->access_token;
                $chat->save();

                foreach($request['responsibles'] as $u)
                {
                    $chat_member = new ChatMember;
                    $chat_member->chat_id = $chat->id;
                    $chat_member->user_id = $u['id'];
                    $chat_member->save();
                }
            }
            else
            {
                foreach($request['responsibles'] as $u)
                {
                    if (!$chat->members->where('service_referrer_id', null)->contains('user_id', $u['id']))
                    {
                        $chat_member = new ChatMember;
                        $chat_member->chat_id = $chat->id;
                        $chat_member->user_id = $u['id'];
                        $chat_member->save();
                    }
                }

                foreach($chat->members->where('service_referrer_id', null) as $member)
                {
                    if (!collect($request['responsibles'])->contains('id', $member->user_id))
                    {
                        $messages = ChatMessage::where('chat_id', $chat->id)->where('chat_member_id', $member->id)->get();
                        foreach($messages as $mes){
                            $mes->delete();
                        }
                        $member->delete();
                    }
                }
            }

            if ($gs->telegram_enabled == true){
                $tg_ids = [];
                foreach($request['responsibles'] as $u)
                {
                    $user = User::find($u['id']);
                    if ($user->tg_id != null && $user->tg_id != ''){
                        array_push($tg_ids, 'user#'.$user->tg_id);
                    }
                }
                $tg_helper = new TelegramMadelineProto;
                if ($tg_helper->create_chat($service->estimate_number. ' ' .$service->name, $tg_ids) != 'ok'){
                    throw new Exception("Telegram error");
                }                      
            }

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }
}
