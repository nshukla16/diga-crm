<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingEstimateToUserTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_planning_user_tasks', function (Blueprint $table) {
            $table->string('estimate_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_planning_user_tasks', function (Blueprint $table) {
            $table->dropColumn('estimate_id')->nullable();
        });
    }
}
