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

class ChatCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id;
    public $chat;

    public function __construct($user, $chat)
    {
        $this->user_id = $user->id;
        $this->chat = $chat;
    }

    public function broadcastOn()
    {
        return new PrivateChannel(EventChannel::pusher_user_channel($this->user_id));
    }
}
