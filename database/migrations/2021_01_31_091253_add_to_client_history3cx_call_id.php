<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToClientHistory3cxCallId extends Migration
{
    public function up()
    {
        Schema::table('client_history', function (Blueprint $table) {
            $table->integer('call3cx_id')->unsigned()->nullable();
            $table->foreign('call3cx_id')->references('id')->on('calls_3cx');
        });
    }

    public function down()
    {
        Schema::table('client_history', function (Blueprint $table) {
            $table->dropForeign(['call3cx_id']);
            $table->dropColumn('call3cx_id');
        });
    }
}
