<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDischargeAndLoadingDateInInvoices extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->datetime('loading_date')->nullable()->change();
            $table->datetime('discharge_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('loading_date')->nullable()->change();
            $table->dropColumn('discharge_date');
        });
    }
}
