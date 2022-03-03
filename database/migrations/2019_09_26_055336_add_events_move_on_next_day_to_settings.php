<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventsMoveOnNextDayToSettings extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('move_events_to_next_day')->default(false);
            $table->string('move_events_to_next_day_time');
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('move_events_to_next_day');
            $table->dropColumn('move_events_to_next_day_time');
        });
    }
}
