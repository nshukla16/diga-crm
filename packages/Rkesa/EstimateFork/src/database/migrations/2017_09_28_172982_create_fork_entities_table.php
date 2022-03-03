<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForkEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('fork_entities')) {
            Schema::create('fork_entities', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('estimate_fork_id');
                $table->integer('object'); // 1 - Data, 2 - Ficha
                $table->integer('order');
                $table->string('category');
                $table->string('subcategory');
                //
                $table->index('estimate_fork_id');
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
        Schema::dropIfExists('fork_entities');
    }
}
