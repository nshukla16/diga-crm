<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fork_entity_id');
            $table->integer('change_type'); // 1 - change description, 2 - change quantity, 3 - add resource, 4 - change resource
            $table->double('price');
            $table->double('quantity');
            $table->double('correction');
            $table->integer('resource_id')->nullable();
            $table->integer('ficha_id')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->integer('estimate_unit_id')->nullable();
            // Indexes
            $table->index('fork_entity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_changes');
    }
}
