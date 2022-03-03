<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeInvoiceCodesToProducts extends Migration
{
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['invoice_code_id']);

            $table->dropColumn('invoice_code_id');
        });

        Schema::rename('invoice_codes', 'products');

        Schema::table('products', function (Blueprint $table) {
            $table->string('code')->unique()->change();
            $table->float('quanity')->default(1);
            $table->float('price')->default(1);

            $table->integer('resource_id')->unsigned()->nullable();
            $table->integer('estimate_unit_id')->unsigned()->nullable();
            $table->integer('vat_type_id')->unsigned()->nullable();

            $table->foreign('resource_id')->references('id')->on('resources');
            $table->foreign('estimate_unit_id')->references('id')->on('estimate_units');
            $table->foreign('vat_type_id')->references('id')->on('vat_types');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropForeign(['resource_id']);
            $table->dropColumn('resource_id');

            $table->dropForeign(['estimate_unit_id']);
            $table->dropColumn('estimate_unit_id');

            $table->dropForeign(['vat_type_id']);
            $table->dropColumn('vat_type_id');

            $table->dropColumn('code');
            $table->dropColumn('quanity');
            $table->dropColumn('price');
        });

        Schema::rename('products', 'invoice_codes');

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->integer('invoice_code_id')->unsigned()->nullable();

            $table->foreign('invoice_code_id')->references('id')->on('invoice_codes');
        });
    }
}
