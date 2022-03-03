<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->integer('limit_type')->nullable();
            $table->integer('limit_forming_type')->nullable();
            $table->integer('limit_forming_date')->nullable();
            $table->integer('limit_forming_days')->nullable();
            $table->dateTimeTz('limit_before_date')->nullable();
            $table->dateTimeTz('date_of_prepayment')->nullable(); // removed in next migrations
            $table->dateTimeTz('date_of_sign_contract')->nulable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
