<?php

use App\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlanningModules extends Migration
{
    public function up()
    {
        DB::table('modules')->insert([
            ['name' => 'project_schedules', 'enabled' => false],
        ]);
    }

    public function down()
    {
        $module = Module::where('name', 'project_schedules')->first();
        $module->delete();
    }
}
