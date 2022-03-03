<?php

use App\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertIntoModulesExpences extends Migration
{
    public function up()
    {
        DB::table('modules')->insert([
            ['name' => 'expences', 'enabled' => true],
        ]);
    }

    public function down()
    {
        $module = Module::where('name', 'expences')->first();
        $module->delete();
    }
}
