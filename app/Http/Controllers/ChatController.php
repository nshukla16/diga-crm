<?php

namespace App\Http\Controllers;

use App\Chat;
use App\ChatMember;
use App\Events\ChatCreatedEvent;
use Exception;
use Log;
use Auth;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $user = Auth::user();
            $res->chats = $user->chats()->with('members')->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $user = Auth::user();
            $chat = Chat::create($request->all());

            foreach($request['members_ids'] as $m){
                $member = new ChatMember;
                $member->user_id = $m;
                $member->chat_id = $chat->id;
                $member->save();
            }

            $chat->load('members');
            $chat->load('messages');

            foreach($chat->members as $member) {
                if ($member->user_id !== $user->id) {
                    broadcast(new ChatCreatedEvent($member->user, $chat));
                }
            }

            $res->chat = $chat;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
