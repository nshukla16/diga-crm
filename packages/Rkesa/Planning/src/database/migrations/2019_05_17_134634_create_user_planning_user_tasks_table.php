<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPlanningUserTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_planning_user_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_planning_user_id');
            $table->dateTimeTz('start');
            $table->dateTimeTz('end');
            $table->string('title');
            $table->text('description');
            $table->string('color');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_planning_user_tasks');
    }
}
