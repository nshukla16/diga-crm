<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFilesListToChatMessages extends Migration
{
    public function up()
    {
        Schema::create('chat_message_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chat_message_id');
            $table->string('file_url');
            $table->string('file_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('chat_message_files');
    }
}
