<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommissionPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_manufacturer_id');
            $table->double('percent');
            $table->double('price');
            $table->string('currency');
            $table->dateTimeTz('payment_date');
            $table->double('in_main_currency');
            $table->boolean('invoice_sent');
            $table->dateTimeTz('invoice_sent_at');
            $table->string('invoice_file')->nullable();
            $table->string('accounting_statement_file')->nullable();
            $table->string('invoice_file_name')->nullable();
            $table->string('accounting_statement_file_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commission_payments');
    }
}
