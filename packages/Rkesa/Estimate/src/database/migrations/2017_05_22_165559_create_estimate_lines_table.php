<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimate_lines')) {
            Schema::create('estimate_lines', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('estimate_id');
                $table->integer('lineable_id');
                $table->string('lineable_type');
                $table->integer('order');
                $table->integer('parent_id')->nullable();
                $table->timestamps();
                // Indexes
                $table->index('estimate_id');
                $table->index('parent_id');
                $table->index(['lineable_id', 'lineable_type']);
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
        Schema::dropIfExists('estimate_lines');
    }
}
