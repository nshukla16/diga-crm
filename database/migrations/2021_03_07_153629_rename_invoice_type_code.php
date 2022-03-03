<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameInvoiceTypeCode extends Migration
{
    public function up()
    {
        DB::table('invoice_items')->delete();
        DB::table('invoices')->delete();
        DB::table('invoice_document_types')->delete();

        DB::table('invoice_document_types')->insert([
            ['code' => 'FS', 'name' => 'Fatura Simplificada', 'increment' => 1],
            ['code' => 'FT', 'name' => 'Fatura', 'increment' => 1],
            ['code' => 'FR', 'name' => 'Fatura-Recibo', 'increment' => 1],

            ['code' => 'ND', 'name' => 'Nota de Débito', 'increment' => 1],
            ['code' => 'NC', 'name' => 'Nota de Crédito', 'increment' => 1],

            ['code' => 'GT', 'name' => 'Guia de Transporte', 'increment' => 1],
            ['code' => 'GR', 'name' => 'Guia de Remessa', 'increment' => 1],

            ['code' => 'ORC', 'name' => 'Orçamento', 'increment' => 1],
            ['code' => 'FP', 'name' => 'Fatura Pro Forma', 'increment' => 1],
        ]);
    }

    public function down()
    {
        DB::table('invoice_document_types')->delete();

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
    }
}
