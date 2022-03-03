<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserPaymentsAddDataInvoiceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->string('data');
            $table->integer('type');
            $table->string('invoice_file')->nullable();
            $table->string('invoice_file_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->dropColumn('data');
            $table->dropColumn('type');
            $table->dropColumn('invoice_file');
            $table->dropColumn('invoice_file_name');
        });
    }
}
