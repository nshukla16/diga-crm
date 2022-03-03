<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToRoadmapTaskAutoEmail extends Migration
{
    public function up()
    {
        Schema::table('user_planning_user_tasks', function (Blueprint $table) {
            $table->boolean('email_auto_send')->nullable(true);
            $table->boolean('attach_invoice')->nullable(true);
        });
    }

    public function down()
    {
        Schema::table('user_planning_user_tasks', function (Blueprint $table) {
            $table->dropColumn('email_auto_send');
            $table->dropColumn('attach_invoice');
        });
    }
}
