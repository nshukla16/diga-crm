<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateLineCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimate_line_categories')) {
            Schema::create('estimate_line_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->boolean('is_pattern');
                $table->integer('category_id')->nullable();
                // Indexes
                $table->index('category_id');
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
        Schema::dropIfExists('estimate_line_categories');
    }
}
