<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManufacturerSpecificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacturer_specifications', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_manufacturer_id');
            $table->string('number');
            $table->string('file')->nullable();
            $table->string('file_name')->nullable();
            $table->dateTimeTz('signed_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacturer_specifications');
    }
}
