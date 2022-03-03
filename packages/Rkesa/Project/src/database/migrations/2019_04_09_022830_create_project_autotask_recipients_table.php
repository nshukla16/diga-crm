<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectAutotaskRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_autotask_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('type');
            $table->integer('group_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('event_type_id');
            $table->integer('project_autotask_id');
            $table->integer('event_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_autotask_recipients');
    }
}
