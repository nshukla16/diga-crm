<?php

namespace App;

use App\ChatMessageFile;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public function files()
    {
        return $this->hasMany(ChatMessageFile::class);
    }
}
