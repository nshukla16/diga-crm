<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlatformIdToServices extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('platform_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('platform_id');
        });
    }
}
