<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUrlAndPathToServiceStateActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_state_actions', function (Blueprint $table) {
            $table->string('url')->nullable(); // for open in browser
            $table->string('path')->nullable(); // for folders for example in google drive
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_state_actions', function (Blueprint $table) {
            $table->dropColumn(['url', 'path']);
        });
    }
}
