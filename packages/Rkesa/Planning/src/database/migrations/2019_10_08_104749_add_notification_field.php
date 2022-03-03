<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotificationField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_planning_user_tasks', function (Blueprint $table) {
            $table->text('notification_about_changes')->nullable(true);
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
            $table->dropColumn('notification_about_changes')->nullable(false);
        });
    }
}
