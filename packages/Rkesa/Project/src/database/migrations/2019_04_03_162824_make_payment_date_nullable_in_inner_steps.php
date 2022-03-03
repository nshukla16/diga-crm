<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakePaymentDateNullableInInnerSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inner_payments', function (Blueprint $table) {
            $table->dateTimeTz('payment_date')->nullable(true)->change();
        });
        Schema::table('commission_payments', function (Blueprint $table) {
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
        Schema::table('inner_payments', function (Blueprint $table) {
            $table->dateTimeTz('payment_date')->nullable(false)->change();
        });
        Schema::table('commission_payments', function (Blueprint $table) {
            $table->dateTimeTz('payment_date')->nullable(false)->change();
        });
    }
}
