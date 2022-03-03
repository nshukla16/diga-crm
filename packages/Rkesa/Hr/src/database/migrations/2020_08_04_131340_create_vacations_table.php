<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVacationsTable extends Migration
{
    public function up()
    {
        Schema::create('vacations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id');
            $table->date('start');
            $table->date('end');
            $table->boolean('is_approved');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vacations');
    }
}
