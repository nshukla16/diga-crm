<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractorServiceStateToGlobalSettings extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->integer('contractor_service_state_id')->unsigned()->nullable();

            $table->foreign('contractor_service_state_id')->references('id')->on('service_states');
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropForeign(['contractor_service_state_id']);

            $table->dropColumn('contractor_service_state_id');
        });
    }
}
