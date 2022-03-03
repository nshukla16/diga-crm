<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelegramFieldsToSettings extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('telegram_enabled')->nullable();
            $table->integer('tg_api_id')->nullable();
            $table->string('tg_api_hash')->nullable();
            $table->string('tg_phone')->nullable();
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('telegram_enabled');
            $table->dropColumn('tg_api_id');
            $table->dropColumn('tg_api_hash');
            $table->dropColumn('tg_phone');
        });
    }
}
