<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_equipments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->integer('manufacturer_id')->nullable();
            $table->string('manufacturer_name');
            $table->string('name');
            $table->integer('estimate_unit_id');
            $table->double('size');
            $table->string('model');
            $table->string('vendor_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_equipments');
    }
}
