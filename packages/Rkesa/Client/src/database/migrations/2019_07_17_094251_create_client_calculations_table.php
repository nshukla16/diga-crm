<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientCalculationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_calculations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->string('calculation_file_path');
            $table->string('calculation_file_name');
            $table->string('calculation_name');
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
        Schema::dropIfExists('client_calculations');
    }
}
