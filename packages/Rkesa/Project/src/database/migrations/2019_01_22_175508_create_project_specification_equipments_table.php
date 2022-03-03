<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectSpecificationEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_specification_equipments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_specification_id');
            $table->string('name');
            $table->double('size');
            $table->integer('estimate_unit_id');
            $table->string('model');
            $table->string('vendor_code');
            $table->double('count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_specification_equipments');
    }
}
