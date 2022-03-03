<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTariffModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tariff_module', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tariff_id')->unsigned();
            $table->integer('module_id')->unsigned();
            $table->timestamps();

            $table->foreign('tariff_id')->references('id')->on('tariffs');
            $table->foreign('module_id')->references('id')->on('modules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tariff_module');
    }
}
