<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeContractPaymentFilesNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_payments', function (Blueprint $table) {
            $table->string('invoice_file')->nullable(true)->change();
            $table->string('accounting_statement_file')->nullable(true)->change();
            $table->string('invoice_file_name')->nullable(true)->change();
            $table->string('accounting_statement_file_name')->nullable(true)->change();
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
            $table->string('invoice_file')->nullable(false)->change();
            $table->string('accounting_statement_file')->nullable(false)->change();
            $table->string('invoice_file_name')->nullable(false)->change();
            $table->string('accounting_statement_file_name')->nullable(false)->change();
        });
    }
}
