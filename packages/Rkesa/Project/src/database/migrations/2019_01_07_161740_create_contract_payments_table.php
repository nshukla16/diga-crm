<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_id');
            $table->double('percent');
            $table->string('currency');
            $table->dateTimeTz('payment_date');
            $table->double('in_main_currency');
            $table->boolean('invoice_sent');
            $table->dateTimeTz('invoice_sent_at');
            $table->string('invoice_file');
            $table->string('accounting_statement_file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_payments');
    }
}
