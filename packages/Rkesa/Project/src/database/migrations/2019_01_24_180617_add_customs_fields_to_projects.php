<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomsFieldsToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('customs_application');
            $table->dateTimeTz('customs_application_date')->nullable();
            $table->boolean('customs_issue');
            $table->dateTimeTz('customs_issue_date')->nullable();
            $table->string('dt');
            $table->string('dt_file')->nullable();
            $table->string('dt_file_name')->nullable();
            $table->double('transportation_total');
            $table->integer('approximate_week_of_arrival');
            $table->dateTimeTz('approximate_date_of_arrival');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['customs_application', 'customs_application_date', 'customs_issue', 'customs_issue_date', 'dt',
                'dt_file', 'dt_file_name', 'transportation_total', 'approximate_week_of_arrival', 'approximate_date_of_arrival']);
        });
    }
}
