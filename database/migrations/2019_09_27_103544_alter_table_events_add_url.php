<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEventsAddUrl extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('url')->nullable();
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
}
