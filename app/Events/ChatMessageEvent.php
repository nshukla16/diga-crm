<?php

namespace App\Events;

use App\ChatMessage;
use Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id; // receiver user id is used only for constructing channel id
    public $message;

    public function __construct($user, ChatMessage $message)
    {
        $this->user_id = $user->id;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel(EventChannel::pusher_user_channel($this->user_id));
    }
}
