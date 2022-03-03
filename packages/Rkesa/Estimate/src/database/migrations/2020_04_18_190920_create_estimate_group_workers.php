<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateGroupWorkers extends Migration
{
    public function up()
    {
        Schema::create('estimate_group_workers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id');
            $table->integer('estimate_group_id');
            $table->date('date');
            $table->dateTime('date_start_before_lunch');
            $table->dateTime('date_end_before_lunch');
            $table->dateTime('date_start_after_lunch');
            $table->dateTime('date_end_after_lunch');
            $table->integer('estimate_line_category_id');
            $table->integer('estimate_unit_id');
            $table->float('quantity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estimate_group_workers');
    }
}
