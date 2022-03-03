<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimesToGroups extends Migration
{
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->time('day_start_time')->default('09:00:00');
            $table->time('lunch_time')->default('13:00:00');
            $table->time('day_finish_time')->default('18:00:00');
            $table->string('working_days')->default('[1, 2, 3, 4, 5]');
        });
    }

    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('day_start_time');
            $table->dropColumn('lunch_time');
            $table->dropColumn('day_finish_time');
            $table->dropColumn('working_days');
        });
    }
}
