<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterServicesTableDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function($table) {
            $table->timestampTz('work_initial_date')->nullable();
            $table->timestampTz('work_final_date')->nullable();
            $table->timestampTz('law_process_date')->nullable();
            $table->string('law_process_number')->nullable();
            $table->string('law_number')->nullable();
            $table->timestampTz('law_initial_date')->nullable();
            $table->timestampTz('law_final_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function($table) {
            $table->dropColumn(['work_initial_date','work_final_date','law_process_date', 'law_process_number', 'law_number', 'law_initial_date', 'law_final_date']);
        });
    }
}
