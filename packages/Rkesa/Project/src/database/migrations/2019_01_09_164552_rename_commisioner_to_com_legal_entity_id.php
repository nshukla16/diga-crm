<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCommisionerToComLegalEntityId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['commissioner']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('commissioner_legal_entity_id')->nullable();
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
            $table->string('commissioner');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['commissioner_legal_entity_id']);
        });
    }
}
