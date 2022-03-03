<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('entity_rules')) {
            Schema::create('entity_rules', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('fork_entity_id');
                $table->integer('field');
                $table->integer('rule_type');
                $table->string('subject');
                //
                $table->index('fork_entity_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_rules');
    }
}
