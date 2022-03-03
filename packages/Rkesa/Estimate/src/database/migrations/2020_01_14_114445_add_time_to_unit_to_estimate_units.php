<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeToUnitToEstimateUnits extends Migration
{
    public function up()
    {
        Schema::table('estimate_units', function (Blueprint $table) {
            $table->float('hours_to_do')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_units', function (Blueprint $table) {
            $table->dropColumn('hours_to_do');
        });
    }
}
