<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->dateTimeTz('start')->nullable();
            $table->text('description')->nullable();
            $table->string('title');
            $table->integer('estimate_id')->nullable();
            $table->decimal('amount');
            $table->integer('client_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('estimate_pay_stage_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_events');
    }
}
