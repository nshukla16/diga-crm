<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKpiUsersAndGroupsTypes extends Migration
{
    public function up()
    {
        Schema::create('kpi_users_and_groups_types', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('kpi_user_and_group_id')->unsigned();
            $table->integer('type_id')->unsigned();
            $table->string('additional_params');
            $table->double('plan_amount');

            $table->foreign('kpi_user_and_group_id')->references('id')->on('kpi_users_and_groups');
            $table->foreign('type_id')->references('id')->on('kpi_types');
        });
    }

    public function down()
    {
        Schema::drop('kpi_user_and_group_types');
    }
}
