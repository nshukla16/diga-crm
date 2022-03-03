<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLegalEntities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('legal_entities', function (Blueprint $table) {
            $table->string('tax_number');
            $table->string('address');
            $table->string('kpp_number');
            $table->string('bank_name');
            $table->string('bic');
            $table->string('bank_receiver_number');
            $table->string('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('legal_entities', function (Blueprint $table) {
            $table->dropColumn(['tax_number', 'address', 'kpp_number', 'bank_name', 'bic', 'bank_receiver_number', 'bank_account_number']);
        });
    }
}
