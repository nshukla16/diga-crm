<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateLineDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimate_line_datas')) {
            Schema::create('estimate_line_datas', function (Blueprint $table) {
                $table->increments('id');
                $table->text('description')->nullable();
                $table->text('note');
                $table->integer('estimate_unit_id')->nullable();
                $table->double('price')->nullable();
                $table->double('quantity')->nullable();
                $table->double('ppu')->nullable();
                $table->boolean('is_pattern');
                // Indexes
                $table->index('estimate_unit_id');
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
        Schema::dropIfExists('estimate_line_datas');
    }
}
