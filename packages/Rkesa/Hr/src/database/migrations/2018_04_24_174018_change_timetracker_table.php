<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTimetrackerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timetracker', function (Blueprint $table) {
            $table->dateTimeTz('start');
            $table->dateTimeTz('finish')->nullable();
            $table->renameColumn('employee_id', 'user_id');
            $table->dropColumn('checkpoint');
            $table->dropColumn('overdue');
            $table->string('photo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timetracker', function (Blueprint $table) {
            $table->dropColumn(['start', 'finish', 'photo']);
            $table->renameColumn('user_id', 'employee_id');
            $table->timestamp('checkpoint')->nullable();
            $table->boolean('overdue');
        });
    }
}
