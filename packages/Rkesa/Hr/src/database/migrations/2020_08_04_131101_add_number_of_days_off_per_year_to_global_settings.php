<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumberOfDaysOffPerYearToGlobalSettings extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->integer('vacation_days_per_year')->nullable();
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('vacation_days_per_year');
        });
    }
}
