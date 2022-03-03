<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecklistEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_entities', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('checklist_id');
            $table->string('name');
            $table->integer('order');
            $table->string('color');
            // Indexes
            $table->index('checklist_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checklist_entities');
    }
}
