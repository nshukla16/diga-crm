<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContactReferrerIdToChatMemebers extends Migration
{
    public function up()
    {
        Schema::table('chat_members', function (Blueprint $table) {
            $table->integer('service_referrer_id')->nullable();
            $table->integer('user_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('chat_members', function (Blueprint $table) {
            $table->dropColumn('service_referrer_id');
            $table->integer('user_id')->change();
        });
    }
}
