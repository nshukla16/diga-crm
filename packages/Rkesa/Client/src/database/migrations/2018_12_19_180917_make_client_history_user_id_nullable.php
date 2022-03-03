<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeClientHistoryUserIdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_history', function($table) {
            $table->integer('user_id')->nullable()->change();
        });
        DB::table('client_history')
            ->where('user_id', 0)
            ->update(['user_id' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_history', function (Blueprint $table) {
            $table->integer('user_id')->nullable(false)->change();
        });
    }
}
