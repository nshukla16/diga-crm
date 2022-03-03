<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateLineWorkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_line_workers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('estimate_line_id');
            $table->integer('user_id');
            $table->timestamps();
            //Indexes
            $table->index('estimate_line_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_line_workers');
    }
}
