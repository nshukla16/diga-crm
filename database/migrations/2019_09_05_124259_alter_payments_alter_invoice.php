<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPaymentsAlterInvoice extends Migration
{
    public function up()
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->text('invoice_file')->nullable(true)->change();
        });
    }

    public function down()
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->text('invoice_file')->nullable(false)->change();
        });
    }
}
