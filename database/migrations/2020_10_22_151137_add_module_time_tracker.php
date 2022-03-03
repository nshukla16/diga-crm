<?php

use App\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModuleTimeTracker extends Migration
{
    public function up()
    {
        DB::table('modules')->insert([
            ['name' => 'time_tracker', 'enabled' => false],
        ]);
        DB::table('modules')->insert([
            ['name' => 'kpi', 'enabled' => false],
        ]);
        DB::table('modules')->insert([
            ['name' => 'financial_calendar', 'enabled' => true],
        ]);
    }

    public function down()
    {
        $module = Module::where('name', 'time_tracker')->first();
        $module->delete();
        
        $kpi = Module::where('name', 'kpi')->first();
        $kpi->delete();

        $fc = Module::where('name', 'financial_calendar')->first();
        $fc->delete();
    }
}
