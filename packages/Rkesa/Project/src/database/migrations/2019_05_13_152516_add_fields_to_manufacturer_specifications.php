<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToManufacturerSpecifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manufacturer_specifications', function (Blueprint $table) {
            $table->boolean('from_db');
            $table->integer('manufacturer_contract_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manufacturer_specifications', function (Blueprint $table) {
            $table->dropColumn(['from_db', 'manufacturer_contract_id']);
        });
    }
}
