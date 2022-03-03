<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EstimateGroupWorkersAddEstimateId extends Migration
{
    public function up()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->integer('estimate_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->dropColumn('estimate_id');
        });
    }
}
