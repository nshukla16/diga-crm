<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConnectionAndExtarnalIdToClientContacts extends Migration
{
    public function up()
    {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->integer('source_id')->nullable();

            $table->integer('connection_id')->unsigned()->nullable();

            $table->foreign('connection_id')->references('id')->on('connections');
        });
    }

    public function down()
    {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->dropColumn('source_id');

            $table->dropForeign(['connection_id']);

            $table->dropColumn('connection_id');
        });
    }
}
