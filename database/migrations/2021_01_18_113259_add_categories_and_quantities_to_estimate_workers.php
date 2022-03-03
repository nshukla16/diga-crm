<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoriesAndQuantitiesToEstimateWorkers extends Migration
{
    public function up()
    {
        Schema::create('estimate_group_workers_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('estimate_group_worker_id')->unsigned()->nullable();

            $table->integer('resource_id')->unsigned()->nullable();
            $table->integer('estimate_line_category_id')->unsigned()->nullable();
            $table->integer('estimate_unit_id')->unsigned()->nullable();
            $table->float('quantity')->nullable();

            $table->foreign('estimate_group_worker_id', 'fk_egw_egwa')->references('id')->on('estimate_group_workers');
            $table->foreign('resource_id')->references('id')->on('resources');
            $table->foreign('estimate_line_category_id', 'fk_egw_elc')->references('id')->on('estimate_line_categories');
            $table->foreign('estimate_unit_id')->references('id')->on('estimate_units');
        });

        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->dropColumn('estimate_line_category_id');
            $table->dropColumn('estimate_unit_id');
            $table->dropColumn('quantity');
            // $table->dropColumn('resource_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('estimate_group_workers_activities');

        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->integer('estimate_line_category_id')->nullable();
            // $table->integer('resource_id')->nullable();
            $table->integer('estimate_unit_id')->nullable();
            $table->float('quantity')->nullable();
        });
    }
}
