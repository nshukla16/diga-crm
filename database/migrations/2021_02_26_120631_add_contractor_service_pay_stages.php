<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractorServicePayStages extends Migration
{
    public function up()
    {
        Schema::create('contractor_service_pay_stages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('text')->nullable();
            $table->date('payment_date')->nullable();
            $table->float('percent')->nullable();
            $table->boolean('paid')->nullable();
            $table->float('fact_paid')->nullable();
            $table->string('invoice_file')->nullable();
            $table->float('invoice_file_name')->nullable();

            $table->integer('source_id')->nullable();
            $table->integer('connection_id')->unsigned()->nullable();
            $table->integer('service_id')->unsigned()->nullable();

            $table->foreign('connection_id')->references('id')->on('connections');
            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contractor_service_pay_stages');
    }
}
