<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['name', 'type', 'service_token'];

    protected $appends = ['not_read_count']; // 'members_ids',

//    protected $hidden = ['members'];

    public function members()
    {
        return $this->hasMany(ChatMember::class);
    }

//    public function members_ids() // contains user ids (not member ids)
//    {
//        return $this->members->pluck('user_id')->all();
//    }
//
//    public function getMembersIdsAttribute()
//    {
//        return $this->members_ids();
//    }

    public function getNotReadCountAttribute()
    {
        $user = Auth::user();
        if ($user) {
            $chat_member_id = ChatMember::where(['user_id' => $user->id, 'chat_id' => $this->id])->first()->id;
            return ChatMembersChatMessages::where('chat_member_id', $chat_member_id)->whereNull('read_at')->count();
        } else { // when we broadcast a message from ChatController@store through queue user is null
            return 0;
        }
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
