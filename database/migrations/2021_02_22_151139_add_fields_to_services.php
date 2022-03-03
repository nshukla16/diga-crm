<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToServices extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('source_id')->nullable();
            $table->date('work_start')->nullable();
            $table->date('work_end')->nullable();
            $table->string('contractor_status')->nullable();

            $table->text('contractor_file')->nullable();
            $table->string('contractor_file_name')->nullable();

            $table->integer('connection_id')->unsigned()->nullable();

            $table->foreign('connection_id')->references('id')->on('connections');
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('source_id');
            $table->dropColumn('work_start');
            $table->dropColumn('work_end');
            $table->dropColumn('contractor_status');

            $table->dropColumn('contractor_file');
            $table->dropColumn('contractor_file_name');

            $table->dropForeign(['connection_id']);

            $table->dropColumn('connection_id');
        });
    }
}
