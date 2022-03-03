<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableChecklistAreas extends Migration
{
    public function up()
    {
        Schema::create('checklist_areas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('checklist_id');
            $table->text('text');
            $table->integer('order');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('checklist_areas');
    }
}
