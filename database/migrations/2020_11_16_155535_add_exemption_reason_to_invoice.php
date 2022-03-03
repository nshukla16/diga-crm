<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExemptionReasonToInvoice extends Migration
{
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['vat_exemption_reason_id']); 

            $table->dropColumn('vat_exemption_reason_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('vat_exemption_reason_id')->unsigned()->nullable();

            $table->foreign('vat_exemption_reason_id')->references('id')->on('vat_exemption_reasons');
        });
    }

    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('vat_exemption_reason_id')->unsigned()->nullable();

            $table->foreign('vat_exemption_reason_id')->references('id')->on('vat_exemption_reasons');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['vat_exemption_reason_id']); 

            $table->dropColumn('vat_exemption_reason_id');
        });
    }
}
