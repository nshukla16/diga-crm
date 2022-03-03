<?php

use App\Module;
use App\Tariff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertTarifsAndTarifModule extends Migration
{
    public function up()
    {
        DB::table('modules')->insert([
            ['name' => 'forms_integration', 'enabled' => true],
        ]);

        $m1 = Module::where('name', 'dashboard')->first();
        $m2 = Module::where('name', 'crm')->first();
        $m3 = Module::where('name', 'hr')->first();
        $m4 = Module::where('name', 'estimate')->first();
        $m5 = Module::where('name', 'forms_integration')->first();
        $m6 = Module::where('name', 'calendar')->first();

        Schema::table('tariffs', function (Blueprint $table) {
            $table->integer('number_of_users')->nullable();
        });

        DB::table('tariffs')->insert([
            ['name' => 'base', 'price' => 20, 'number_of_users' => 2],
        ]);

        $t = Tariff::where('name', 'base')->first();

        DB::table('tariff_module')->insert([
            ['tariff_id' => $t->id, 'module_id' => $m1->id],
            ['tariff_id' => $t->id, 'module_id' => $m2->id],
            ['tariff_id' => $t->id, 'module_id' => $m3->id],
            ['tariff_id' => $t->id, 'module_id' => $m4->id],
            ['tariff_id' => $t->id, 'module_id' => $m5->id],
            ['tariff_id' => $t->id, 'module_id' => $m6->id],
        ]);       
    }

    public function down()
    {
        $module = Module::where('name', 'forms_integration')->first();
        $module->delete();

        $t = Tariff::where('name', 'base')->first();       
        $t->delete();

        Schema::table('tariffs', function (Blueprint $table) {
            $table->dropColumn('number_of_users');
        });
    }
}
