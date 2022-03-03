<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPHoneAndTgIdToServiceRefferers extends Migration
{
    public function up()
    {
        Schema::table('service_referrers', function (Blueprint $table) {
            $table->string('formatted_cell_phone')->nullable();
            $table->integer('tg_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('service_referrers', function (Blueprint $table) {
            $table->dropColumn('formatted_cell_phone');
            $table->dropColumn('tg_id');
        });
    }
}
