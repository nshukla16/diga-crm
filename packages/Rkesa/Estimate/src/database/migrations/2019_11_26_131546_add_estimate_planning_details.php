<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEstimatePlanningDetails extends Migration
{
    public function up()
    {
        Schema::create('estimate_planning_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('estimate_id');
            $table->integer('days');
            $table->double('start_point_lat');
            $table->double('start_point_lng');
            $table->double('consumption_per_100_km');
            $table->double('gasoline_price');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estimate_planning_details');
    }
}
