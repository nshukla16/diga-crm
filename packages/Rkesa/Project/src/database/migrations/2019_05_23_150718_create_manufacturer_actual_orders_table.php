<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManufacturerActualOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacturer_actual_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('manufacturer_id');
            $table->string('name');
            $table->integer('client_id');
            $table->string('contract_number');
            $table->string('specification_number');
            $table->string('file')->nullable();
            $table->string('file_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacturer_actual_orders');
    }
}
