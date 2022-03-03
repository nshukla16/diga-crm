<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentInvoiceIdToInvoices extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('parent_invoice_id')->unsigned()->nullable();

            $table->foreign('parent_invoice_id')->references('id')->on('invoices');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['parent_invoice_id']); 

            $table->dropColumn('parent_invoice_id');
        });
    }
}
