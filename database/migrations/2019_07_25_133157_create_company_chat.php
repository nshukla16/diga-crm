<?php

use App\Chat;
use App\ChatMember;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyChat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $chat = new Chat;
        $chat->type = 2;
        $chat->name = trans('template.Common_company_chat');
        $chat->save();

        $all_users = User::all();
        foreach($all_users as $user){
            $member = new ChatMember;
            $member->user_id = $user->id;
            $member->chat_id = $chat->id;
            $member->save();
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
