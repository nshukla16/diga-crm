<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKpiUsersAndGroups extends Migration
{
    public function up()
    {
        Schema::create('kpi_users_and_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('group_id')->unsigned()->nullable();
            $table->date('start_date');
            $table->integer('period_id')->unsigned();

            $table->foreign('period_id')->references('id')->on('kpi_periods');
            $table->foreign('group_id')->references('id')->on('groups');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::drop('kpi_users_and_groups');
    }
}
