<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameLesseeToLesseeClientId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['lessee']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('lessee_client_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('lessee');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['lessee_client_id']);
        });
    }
}
