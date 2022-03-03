<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyIdToGroups extends Migration
{
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->integer('client_id')->unsigned()->nullable();

            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['client_id']);

            $table->dropColumn('client_id');
        });
    }
}
