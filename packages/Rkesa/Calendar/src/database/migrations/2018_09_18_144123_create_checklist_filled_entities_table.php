<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecklistFilledEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_filled_entities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('checklist_filled_id');
            $table->integer('checklist_entity_id');
            $table->text('text');
            $table->timestamps();
            // Indexes
            $table->index('checklist_filled_id');
            $table->index('checklist_entity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checklist_filled_entities');
    }
}
