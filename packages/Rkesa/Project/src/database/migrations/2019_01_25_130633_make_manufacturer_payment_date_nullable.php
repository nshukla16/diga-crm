<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeManufacturerPaymentDateNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manufacturer_payments', function (Blueprint $table) {
            $table->dateTimeTz('payment_date')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manufacturer_payments', function (Blueprint $table) {
            $table->dateTimeTz('payment_date')->nullable(false)->change();
        });
    }
}
