<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpences extends Migration
{
    public function up()
    {
        Schema::create('expences', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('invoice_number');
            $table->string('supplier');
            $table->date('date');
            $table->double('total_without_vat');
            $table->string('vat_type');
            $table->double('total');
            $table->text('invoice_file')->nullable();
            $table->string('invoice_file_name')->nullable();
            $table->integer('estimate_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expences');
    }
}
