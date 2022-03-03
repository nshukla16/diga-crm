<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientContactAddCreator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->integer('creator_user_id')->unsigned()->nullable();

            $table->foreign('creator_user_id')->references('id')->on('users');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->integer('creator_user_id')->unsigned()->nullable();

            $table->foreign('creator_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->dropColumn('creator_user_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('creator_user_id');
        });
    }
}
