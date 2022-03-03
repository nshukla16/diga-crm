<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\Events\EventChannel;

Broadcast::channel(EventChannel::pusher_user_channel_template(), function ($user, $id) {
    return (int) $user->id === (int) $id;
});


//Broadcast::channel('*', function ($user) {
Broadcast::channel(EventChannel::pusher_common_channel(), function ($user) {
    return Auth::check();
});

Broadcast::channel(EventChannel::pusher_online_channel(), function ($user) {
    if (Auth::check()){
        return ['id' => $user->id];
    }
});