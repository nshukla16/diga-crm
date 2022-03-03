<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatTypeToEstimatePayStages extends Migration
{
    public function up()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->integer('vat_type')->nullable();
            $table->text('invoice_file')->nullable();
            $table->string('invoice_file_name')->nullable();
            $table->text('recibo_file')->nullable();
            $table->string('recibo_file_name')->nullable();
            $table->smallInteger('paid')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->dropColumn('vat_type');
            $table->dropColumn('invoice_file');
            $table->dropColumn('invoice_file_name');
            $table->dropColumn('recibo_file');
            $table->dropColumn('recibo_file_name');
            $table->dropColumn('paid');
        });
    }
}
