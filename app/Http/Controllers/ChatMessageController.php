<?php

namespace App\Http\Controllers;

use Log;
use Auth;
use App\Chat;
use stdClass;
use Exception;
use Carbon\Carbon;
use App\ChatMember;
use App\ChatMessage;
use App\ChatMessageFile;
use Illuminate\Http\Request;
use App\Mail\NewChatMessages;
use App\ChatMembersChatMessages;
use App\Events\ChatMessageEvent;
use App\Http\Traits\SaasAuthTrait;
use Illuminate\Support\Facades\Mail;
use Rkesa\Service\Models\ServiceReferrer;

class ChatMessageController extends Controller
{
    const NOT_ALLOWED_EXTENTIONS = ['php', 'html', 'js'];

    use SaasAuthTrait;

    public function index(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $user = Auth::user();
            $member = ChatMember::where(['chat_id' => $id, 'user_id' => $user->id])->first();
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

    public function store(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $user = Auth::user();

            $chat_member = ChatMember::where(['chat_id' => $id, 'user_id' => $user->id])->first();

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
                if ($member->user_id !== $user->id) {                    
                    $member_message = new ChatMembersChatMessages;
                    $member_message->chat_member_id = $member->id;
                    $member_message->chat_message_id = $message->id;
                    $member_message->save();
                    if ($member->user_id === 0){ // tech support
                        $this::message_to_tech_support($user->id, $message);
                    }else{
                        if ($member->user_id){
                            broadcast(new ChatMessageEvent($member->user, $message));
                        }
                        else{
                            broadcast(new ChatMessageEvent($member->service_referrer, $message));
                        }                        
                    }

                    if ($member->service_referrer_id){
                        $service_referrer = ServiceReferrer::find($member->service_referrer_id);
                        
                        if ($service_referrer->email_sent_at == null || 
                            Carbon::now()->diffInDays(Carbon::parse($service_referrer->email_sent_at)) >= 1)
                        {
                            $to = [
                                [
                                    'email' => $service_referrer->email, 
                                    'name' => $service_referrer->name,
                                ]
                            ];

                            Mail::to($to)->send(new NewChatMessages($service_referrer, $user->name, $service_referrer->locale, $chat->service_token));

                            $service_referrer->email_sent_at = Carbon::now();
                            $service_referrer->save();
                        }
                    }
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

    public static function message_to_tech_support($user_id, $message)
    {
        $response_decoded = self::get_access_token();

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/techsupport', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => [
                'user_id' => $user_id,
                'message' => json_encode($message)
            ],
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['result'] == 'OK';
    }

    public static function message_from_tech_support($user_id, $message)
    {
        $techsupport_chats = Chat::where('type', 3)->pluck('id');

        $user_chat_member = ChatMember::where('user_id', $user_id)->whereIn('chat_id', $techsupport_chats)->first();

        $tech_chat_member = ChatMember::where(['user_id' => 0, 'chat_id' => $user_chat_member->chat_id])->first();

        $raw_message = json_decode($message);

        $chat_message = new ChatMessage;
        $chat_message->chat_member_id = $tech_chat_member->id;
        $chat_message->chat_id = $tech_chat_member->chat_id;
        $chat_message->message = $raw_message->message;
        $chat_message->save();

        $member_message = new ChatMembersChatMessages;
        $member_message->chat_member_id = $user_chat_member->id;
        $member_message->chat_message_id = $chat_message->id;
        $member_message->save();

        if ($raw_message->files){
            foreach($raw_message->files as $file)
            {
                $message_file = new ChatMessageFile;
                $message_file->chat_message_id = $chat_message->id;
                $message_file->file_url = env('ERP_SAAS_URL', '') . '' . $file->file_url;
                $message_file->file_name = $file->file_name;
                $message_file->save();
            }
        }

        $chat_message = $chat_message->load('files');

        broadcast(new ChatMessageEvent($user_chat_member->user, $chat_message));
    }

    public function make_as_read(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $user = Auth::user();

            $message = ChatMessage::find($id);
            $member = ChatMember::where(['chat_id' => $message->chat_id, 'user_id' => $user->id])->first();

            $cmcm = ChatMembersChatMessages::where(['chat_member_id' => $member->id, 'chat_message_id' => $id])->first();

            $cmcm->read_at = Carbon::now();
            $cmcm->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function file_upload(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
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
