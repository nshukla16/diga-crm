<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricesToPaymentSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_payments', function (Blueprint $table) {
            $table->double('price');
        });
        Schema::table('manufacturer_payments', function (Blueprint $table) {
            $table->double('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contract_payments', function (Blueprint $table) {
            $table->dropColumn(['price']);
        });
        Schema::table('manufacturer_payments', function (Blueprint $table) {
            $table->dropColumn(['price']);
        });
    }
}
