<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFromDbToProjectCarriers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_carriers', function (Blueprint $table) {
            $table->boolean('from_db')->default(false);
            $table->integer('carrier_contract_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_carriers', function (Blueprint $table) {
            $table->dropColumn(['from_db', 'carrier_contract_id']);
        });
    }
}
