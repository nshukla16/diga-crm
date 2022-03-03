<?php

use App\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertModulesCRM extends Migration
{
    public function up()
    {
        DB::table('modules')->insert([
            ['name' => 'crm', 'enabled' => true],
        ]);
    }

    public function down()
    {
        $module = Module::where('name', 'crm')->first();
        $module->delete();
    }
}
