<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('service_states')) {
            Schema::create('service_states', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('order');
                $table->boolean('horizontal')->default(true);
                $table->string('color');
                $table->string('icon');
                $table->integer('type')->default(0); // 0 - state, 1 - button
                // For states
                $table->boolean('can_click')->default(true);
                $table->boolean('with_reason')->default(false);
                // For buttons
                $table->integer('destination_state_id'); // 0 - not specified
                // Indexes
                $table->index('name');
                $table->index(['id', 'name']);
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
            Schema::dropIfExists('service_states');
    }
}
