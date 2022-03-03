<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTimetrackerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timetracker', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->integer('estimate_id')->nullable();
            $table->double('lat');
            $table->double('lng');
            $table->timestamp('checkpoint')->nullable();
            $table->boolean('overdue');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timetracker');
    }
}
