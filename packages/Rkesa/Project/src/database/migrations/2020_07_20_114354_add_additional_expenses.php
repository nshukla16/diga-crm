<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalExpenses extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('additional_expenses')) {
            Schema::create('additional_expenses', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->integer('project_id');
                $table->string('name');
                $table->double('price');
                $table->string('currency');
                $table->datetime('payment_date');
                $table->double('in_main_currency');
                $table->string('document_file')->nullable();
                $table->string('document_file_name')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('additional_expenses');
    }
}
