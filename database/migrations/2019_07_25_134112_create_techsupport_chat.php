<?php

use App\Chat;
use App\ChatMember;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechsupportChat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $all_users = User::all();
        foreach($all_users as $user){
            $chat = new Chat;
            $chat->type = 3;
            $chat->name = trans('template.Tech_support_chat');
            $chat->save();

            $member = new ChatMember;
            $member->user_id = $user->id;
            $member->chat_id = $chat->id;
            $member->save();

            $member2 = new ChatMember;
            $member2->user_id = 0;
            $member2->chat_id = $chat->id;
            $member2->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
