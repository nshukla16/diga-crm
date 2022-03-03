<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateGroupMaterialConsumption extends Migration
{
    public function up()
    {
        Schema::create('estimate_group_materials_consumption', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('estimate_group_id');
            $table->date('date');
            $table->integer('estimate_line_category_id');
            $table->integer('resource_id');
            $table->integer('estimate_unit_id');
            $table->float('quantity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estimate_group_materials_consumption');
    }
}
