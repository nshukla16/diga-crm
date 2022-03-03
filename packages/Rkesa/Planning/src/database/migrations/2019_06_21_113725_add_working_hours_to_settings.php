<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkingHoursToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Setting::create(['key' => 'planning_working_hours_start', 'value' => '9']);
        Setting::create(['key' => 'planning_working_hours_end', 'value' => '18']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where(['key' => 'planning_working_hours_start'])->delete();
        Setting::where(['key' => 'planning_working_hours_end'])->delete();
    }
}
