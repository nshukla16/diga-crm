<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToEstimateGroups extends Migration
{
    public function up()
    {
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->date('work_start')->nullable();
            $table->date('work_end')->nullable();
            $table->string('contractor_status')->nullable();

            $table->text('contractor_file')->nullable();
            $table->string('contractor_file_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->dropColumn('work_start');
            $table->dropColumn('work_end');
            $table->dropColumn('contractor_status');

            $table->dropColumn('contractor_file');
            $table->dropColumn('contractor_file_name');
        });
    }
}
