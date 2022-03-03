<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResponsibleIdAndContactSource extends Migration
{
    public function up()
    {
        Schema::table('connections', function (Blueprint $table) {
            $table->integer('responsible_id')->unsigned()->nullable();
            $table->integer('client_referrer_id')->unsigned()->nullable();

            $table->foreign('responsible_id')->references('id')->on('users');
            $table->foreign('client_referrer_id')->references('id')->on('client_referrers');
        });
    }

    public function down()
    {
        Schema::table('connections', function (Blueprint $table) {
            $table->dropForeign(['responsible_id']);
            $table->dropForeign(['client_referrer_id']);

            $table->dropColumn('responsible_id');
            $table->dropColumn('client_referrer_id');
        });
    }
}
