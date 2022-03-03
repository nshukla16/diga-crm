<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnTypeInServicePayStages extends Migration
{
    public function up()
    {
        Schema::table('contractor_service_pay_stages', function (Blueprint $table) {
            $table->string('invoice_file_name')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('contractor_service_pay_stages', function (Blueprint $table) {
            $table->float('invoice_file_name')->nullable()->change();
        });
    }
}
