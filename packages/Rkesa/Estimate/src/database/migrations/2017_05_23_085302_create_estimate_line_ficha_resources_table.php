<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateLineFichaResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimate_line_ficha_resources')) {
            Schema::create('estimate_line_ficha_resources', function (Blueprint $table) {
                $table->increments('id');
                //$table->string('name');
                $table->double('price');
                $table->integer('estimate_unit_id')->nullable();
                $table->integer('resource_type');
                $table->integer('estimate_line_ficha_id');
                $table->double('correction');
                $table->double('quantity');
                $table->integer('resource_id');
                //$table->boolean('is_pattern');
                $table->timestamps();
                // Indexes
                $table->index('estimate_line_ficha_id');
                $table->index('estimate_unit_id');
                $table->index('resource_id');
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
        Schema::dropIfExists('estimate_line_ficha_resources');
    }
}
