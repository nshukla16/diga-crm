<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColorVariables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('color1')->default('#4D4D4D');
            $table->string('color2')->default('#24C5C3');
            $table->string('color3')->default('#2A6668');
            $table->string('color4')->default('#DDE6E8');
            $table->string('color5')->default('#00ff00');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn(['color1', 'color2', 'color3', 'color4', 'color5']);
        });
    }
}
