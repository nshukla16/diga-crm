<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceToEstimatePayStages extends Migration
{
    public function up()
    {
        Schema::table('estimate_group_pay_stages', function (Blueprint $table) {
            $table->text('invoice_file')->nullable();
            $table->string('invoice_file_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_group_pay_stages', function (Blueprint $table) {
            $table->dropColumn(['invoice_file_name', 'invoice_file']);
        });
    }
}
