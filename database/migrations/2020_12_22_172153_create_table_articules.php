<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableArticules extends Migration
{
    public function up()
    {
        Schema::create('invoice_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('code');
            $table->string('name');

            $table->integer('parent_id')->unsigned()->nullable();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('invoice_code_id')->unsigned()->nullable();

            $table->foreign('invoice_code_id')->references('id')->on('invoice_codes');
        });

        DB::table('invoice_codes')->insert([
            ['code' => 'SERV', 'name' => 'Serviços gerais'],
            ['code' => 'DESP', 'name' => 'Despesa geral'],
        ]);
    }

    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['invoice_code_id']); 

            $table->dropColumn('invoice_code_id');
        });

        Schema::dropIfExists('invoice_codes');
    }
}
