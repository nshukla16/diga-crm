<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityRuleResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_rule_resources', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('field');
            $table->integer('rule_type');
            $table->string('subject');
            $table->integer('entity_rule_id');
            $table->integer('resource_id');
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
        Schema::dropIfExists('entity_rule_resources');
    }
}
