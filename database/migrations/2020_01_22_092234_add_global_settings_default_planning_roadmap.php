<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlobalSettingsDefaultPlanningRoadmap extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->integer('default_planning_roadmap_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('default_planning_roadmap_id');
        });
    }
}
