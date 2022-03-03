<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityChangeResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_change_resources', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_change_id');
            $table->integer('field');
            $table->integer('resource_id')->nullable();
            $table->string('subject');
            // Indexes
            $table->index('resource_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_change_resources');
    }
}
