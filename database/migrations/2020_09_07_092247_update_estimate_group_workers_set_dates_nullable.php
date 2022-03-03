<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEstimateGroupWorkersSetDatesNullable extends Migration
{
    public function up()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->dateTime('date_end_before_lunch')->nullable()->change();
            $table->dateTime('date_start_after_lunch')->nullable()->change();
            $table->dateTime('date_end_after_lunch')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->dateTime('date_end_before_lunch')->change();
            $table->dateTime('date_start_after_lunch')->change();
            $table->dateTime('date_end_after_lunch')->change();
        });
    }
}
