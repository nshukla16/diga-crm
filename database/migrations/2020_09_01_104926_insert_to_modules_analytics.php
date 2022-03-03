<?php

use App\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertToModulesAnalytics extends Migration
{
    public function up()
    {
        DB::table('modules')->insert([
            ['name' => 'analytics', 'enabled' => false],
        ]);
    }

    public function down()
    {
        $module = Module::where('name', 'analytics')->first();
        $module->delete();
    }
}
