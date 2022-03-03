<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatMessageFile extends Model
{
    protected $table = 'chat_message_files';

    protected $fillable = ['chat_message_id', 'file_url', 'file_name'];
}
