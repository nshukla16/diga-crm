<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEstimateGroupPercentToDouble extends Migration
{
    public function up()
    {
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->float('percent')->change();
        });
    }

    public function down()
    {
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->integer('percent')->change();
        });
    }
}
