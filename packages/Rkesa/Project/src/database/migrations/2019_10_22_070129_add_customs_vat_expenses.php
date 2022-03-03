<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomsVatExpenses extends Migration
{
    public function up()
    {
        Schema::create('transportation_vat_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('manufacturer_order_id');
            $table->string('name');
            $table->double('price');
            $table->string('currency');
            $table->dateTimeTz('payment_date');
            $table->double('in_main_currency');
            $table->boolean('invoice_sent');
            $table->string('invoice_file')->nullable();
            $table->string('accounting_statement_file')->nullable();
            $table->boolean('confirmed')->default(false);
            $table->dateTimeTz('confirmed_date')->nullable();
            $table->string('invoice_file_name')->nullable();
            $table->string('accounting_statement_file_name')->nullable();
            $table->string('document_file')->nullable();
            $table->string('document_file_name')->nullable();            
        });
    }

    public function down()
    {
        Schema::dropIfExists('transportation_vat_payments');
    }
}
