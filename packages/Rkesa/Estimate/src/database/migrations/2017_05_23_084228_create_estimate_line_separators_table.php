<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateLineSeparatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimate_line_separators')) {
            Schema::create('estimate_line_separators', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->boolean('is_pattern');
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
        Schema::dropIfExists('estimate_line_separators');
    }
}
