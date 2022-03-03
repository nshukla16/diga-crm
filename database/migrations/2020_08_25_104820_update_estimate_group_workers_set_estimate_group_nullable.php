<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEstimateGroupWorkersSetEstimateGroupNullable extends Migration
{
    public function up()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->integer('estimate_group_id')->nullable()->change();
            $table->integer('estimate_line_category_id')->nullable()->change();
            $table->integer('estimate_unit_id')->nullable()->change();
            $table->float('quantity')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->integer('estimate_group_id')->change();
            $table->integer('estimate_line_category_id')->change();
            $table->integer('estimate_unit_id')->change();
            $table->float('quantity')->change();
        });
    }
}
