<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocumentTypes extends Migration
{
    public function up()
    {
        Schema::create('invoice_document_types', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('code');
            $table->string('name');
            $table->integer('increment')->nullable();
        });

        DB::table('invoice_document_types')->insert([
            ['code' => 'FS', 'name' => 'Fatura Simplificada', 'increment' => 1],
            ['code' => 'FAC', 'name' => 'Fatura', 'increment' => 1],
            ['code' => 'FR', 'name' => 'Fatura-Recibo', 'increment' => 1],
            ['code' => 'ADT', 'name' => 'Fatura-Adiantamento', 'increment' => 1],
            ['code' => 'ND', 'name' => 'Nota de Débito', 'increment' => 1],
            ['code' => 'NC', 'name' => 'Nota de Crédito', 'increment' => 1],
            ['code' => 'NCA', 'name' => 'Nota de Crédito-Adiantamento', 'increment' => 1],
            ['code' => 'NRE', 'name' => 'Nota de Crédito-Reembolso', 'increment' => 1],
            ['code' => 'GT', 'name' => 'Guia de Transporte', 'increment' => 1],
            ['code' => 'GR', 'name' => 'Guia de Remessa', 'increment' => 1],
            ['code' => 'DGT', 'name' => 'Devolução do cliente', 'increment' => 1],
            ['code' => 'NE', 'name' => 'Nota de Encomenda', 'increment' => 1],
            ['code' => 'ORC', 'name' => 'Orçamento', 'increment' => 1],
            ['code' => 'FP', 'name' => 'Fatura Pro Forma', 'increment' => 1],
            ['code' => 'NR', 'name' => 'Nota de reparação', 'increment' => 1],
            ['code' => 'TDE', 'name' => 'Talão Desconto', 'increment' => 1],
            ['code' => 'CNT', 'name' => 'Consumo Contrato', 'increment' => 1],
            ['code' => 'GTG', 'name' => 'Guia de Transporte Global', 'increment' => 1],
        ]);

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('document_type_id')->unsigned()->nullable();

            $table->foreign('document_type_id')->references('id')->on('invoice_document_types');
        });
    }

    public function down()
    {      
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']); 

            $table->dropColumn('document_type_id');
        });

        Schema::dropIfExists('invoice_document_types');
    }
}
