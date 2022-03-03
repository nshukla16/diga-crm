<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFactPaidAndInvoiceNumberToPayStages extends Migration
{
    public function up()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->string('invoice_number')->nullable();
            $table->double('fact_paid')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
            $table->dropColumn('fact_paid');
        });
    }
}
