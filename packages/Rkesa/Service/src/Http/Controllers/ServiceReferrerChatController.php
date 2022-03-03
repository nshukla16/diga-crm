<?php

namespace Rkesa\Service\Http\Controllers;

use DB;
use Log;
use Auth;
use App\Chat;
use Exception;
use Carbon\Carbon;
use App\ChatMember;
use App\ChatMessage;
use App\User as User;
use App\ChatMessageFile;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\MailTrait;
use App\ChatMembersChatMessages;
use App\Events\ChatMessageEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ContactAssigned;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Service\Models\ServiceReferrer;
use App\Http\Controllers\FacebookController;
use App\Http\Traits\ClientAndContactWithTrait;
use Illuminate\Support\Facades\Log as IlluminateLog;

class ServiceReferrerChatController extends Controller
{
    const NOT_ALLOWED_EXTENTIONS = ['php', 'html', 'js'];
    
    public function chats($token)
    {
        $res = (object)array();
        $res->errcode = 0;
        try
        {
            $res->chats = Chat::with('members')->where('service_token', $token)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function messages(Request $request, $hash, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $referrer = ServiceReferrer::where('hash', $hash)->firstOrFail();

            $member = ChatMember::where(['chat_id' => $id, 'service_referrer_id' => $referrer->id])->first();

            if ($member) {
                $message_ids_to_set_read = ChatMessage::where('chat_id', $id)->pluck('id');
                ChatMembersChatMessages::whereIn('chat_message_id', $message_ids_to_set_read)
                    ->where('chat_member_id', $member->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => Carbon::now()]);
                $res->messages = Chat::with('messages.files')->find($id)->messages;
            }else{
                throw new Exception('Forbidden', 403);
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function post(Request $request, $hash, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $referrer = ServiceReferrer::where('hash', $hash)->firstOrFail();

            $chat_member = ChatMember::where(['chat_id' => $id, 'service_referrer_id' => $referrer->id])->first();

            $message = new ChatMessage;
            $message->chat_member_id = $chat_member->id;
            $message->chat_id = $id;
            $message->message = $request['message'];
            $message->save();

            $files = $request['files'];
            foreach($files as $file)
            {
                $message_file = new ChatMessageFile;
                $message_file->chat_message_id = $message->id;
                $message_file->file_url = $file['file_url'];
                $message_file->file_name = $file['file_name'];
                $message_file->save();
            }

            $message = $message->load('files');

            $chat = Chat::with('members')->find($id);
            foreach($chat->members as $member) {

                if ($member->client_contact_referrer_id)
                {
                    if ($member->client_contact_referrer_id !== $referrer->id) {
                        $member_message = new ChatMembersChatMessages;
                        $member_message->chat_member_id = $member->id;
                        $member_message->chat_message_id = $message->id;
                        $member_message->save();
    
                        broadcast(new ChatMessageEvent($member->service_referrer, $message));
                    }
                }
                if ($member->user_id)
                {
                    $member_message = new ChatMembersChatMessages;
                    $member_message->chat_member_id = $member->id;
                    $member_message->chat_message_id = $message->id;
                    $member_message->save();
                    broadcast(new ChatMessageEvent($member->user, $message));
                }
            }
            $res->id = $message->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function file_upload(Request $request, $hash)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $referrer = ServiceReferrer::where('hash', $hash)->firstOrFail();

            $files = $request->file('files');
            $items = [];

            foreach($files as $file)
            {
                $ext = $file->getClientOriginalExtension();
                if (array_search($ext, $this::NOT_ALLOWED_EXTENTIONS) !== false) {
                    throw new Exception('Not allowed file extention');
                }
    
                $name_orig = substr($file->getClientOriginalName(), 0, strrpos($file->getClientOriginalName(), "." . $ext)); // we don't use pathinfo because it is locale aware
                $path = 'img/uploads/temp/';
                $name = uniqid('', true) . '.' . $ext;
                $file->move($path, $name);
                $item = new \stdClass;
                $item->file_url = '/' . $path . $name;
                $item->file_name = $name_orig . '.' . $ext;
                array_push($items, $item);
            }
            $res->files = $items;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

}
