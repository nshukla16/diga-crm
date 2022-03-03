<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMyPercentToEstimatePlanningDetails extends Migration
{
    public function up()
    {
        Schema::table('estimate_planning_details', function (Blueprint $table) {
            $table->float('company_percent')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_planning_details', function (Blueprint $table) {
            $table->dropColumn('company_percent');
        });
    }
}
