<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimatePlanningLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_planning_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('estimate_planning_id');
            $table->integer('estimate_line_id')->nullable();
            $table->dateTimeTz('start_datetime');
            $table->dateTimeTz('end_datetime');
            $table->integer('progress');
            $table->text('description');
            $table->text('predecessor');
            $table->string('line_number');
            $table->string('name')->nullable();
            $table->string('parent_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_planning_lines');
    }
}
