<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('enabled');
        });
        DB::table('modules')->insert([
            ['name' => 'calendar', 'enabled' => true],
            ['name' => 'calendar_extended', 'enabled' => false],
            ['name' => 'client', 'enabled' => true],
            ['name' => 'dashboard', 'enabled' => true],
            ['name' => 'email', 'enabled' => true],
            ['name' => 'estimate', 'enabled' => true],
            ['name' => 'estimate_fork', 'enabled' => true],
            ['name' => 'hr', 'enabled' => true],
            ['name' => 'service', 'enabled' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
}
