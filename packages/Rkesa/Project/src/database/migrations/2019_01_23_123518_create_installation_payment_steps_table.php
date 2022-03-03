<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstallationPaymentStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('installation_payment_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_id');
            $table->string('payment_name')->nullable();
            $table->double('payment_value')->nullable();
            $table->string('payment_currency')->nullable();
            $table->dateTimeTz('payment_date')->nullable();
            $table->boolean('payment_invoice_sent')->nullable();
            $table->double('payment_main_currency')->nullable();
            $table->string('payment_invoice_file_name')->nullable();
            $table->string('payment_invoice_file_path')->nullable();
            $table->string('payment_accounting_file_name')->nullable();
            $table->string('payment_accounting_file_path')->nullable();
            $table->boolean('payment_confirmed')->nullable();
            $table->dateTimeTz('payment_confirmed_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installation_payment_steps');
    }
}
