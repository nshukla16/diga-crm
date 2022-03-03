<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Add3cxEnabledToGlobalSettings extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('cx_enabled')->default(false);
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('cx_enabled');
        });
    }
}
