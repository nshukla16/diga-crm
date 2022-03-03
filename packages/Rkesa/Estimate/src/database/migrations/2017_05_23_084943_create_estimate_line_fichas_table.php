<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateLineFichasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimate_line_fichas')) {
            Schema::create('estimate_line_fichas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->text('note');
                $table->integer('estimate_unit_id')->nullable();
                $table->integer('estimate_line_category_id');
                $table->double('quantity')->nullable();
                $table->boolean('is_pattern');
                $table->double('price');
                $table->double('ppu')->nullable();;
                $table->timestamps();
                // Indexes
                $table->index('estimate_line_category_id');
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
        Schema::dropIfExists('estimate_line_fichas');
    }
}
