<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServiceIdToEstimateGroups extends Migration
{
    public function up()
    {
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->integer('service_id')->unsigned()->nullable();

            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    public function down()
    {
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->dropForeign(['service_id']);

            $table->dropColumn('service_id');
        });
    }
}
