<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZadarmaCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zadarma_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('pbx_call_id');
            $table->integer('internal_number');
            $table->string('event');
            $table->string('caller_id')->nullable();
            $table->string('called_did')->nullable();
            $table->string('call_id_with_rec')->nullable();
            $table->dateTime('call_start')->nullable();
            $table->integer('duration')->nullable();
            $table->string('disposition')->nullable();
            $table->boolean('is_recorded')->default(false);
            $table->string('record_link')->nullable();
            $table->dateTime('record_link_lifetime_till')->nullable();
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zadarma_calls');
    }
}
