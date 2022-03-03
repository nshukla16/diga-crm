<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCalls3CX extends Migration
{
    public function up()
    {
        Schema::create('calls_3cx', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('number');
            $table->string('call_type');
            $table->integer('agent')->nullable();
            $table->string('duration');
            $table->integer('duration_minutes');
            $table->integer('duration_seconds');
            $table->integer('duration_milliseconds');
            $table->integer('client_contact_id')->unsigned()->nullable();

            $table->foreign('client_contact_id')->references('id')->on('client_contacts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('calls_3cx');
    }
}
