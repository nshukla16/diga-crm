<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EventChannel
{
    public static function pusher_common_channel()
    {
        return str_replace(':', '-', str_replace(array('https://', 'http://'), '', env('APP_URL', '')));
    }

    public static function pusher_user_channel($id)
    {
        return EventChannel::pusher_common_channel().'-user-'.$id;
    }

    public static function pusher_user_channel_template()
    {
        return EventChannel::pusher_common_channel().'-user-{id}';
    }

    public static function pusher_online_channel()
    {
        return EventChannel::pusher_common_channel().'-online';
    }
}
