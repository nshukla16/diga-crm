<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailForInvoicesToGlobalSettings extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('invoice_auto_send_email')->nullable();
            $table->string('invoice_auto_send_email_password')->nullable();
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('invoice_auto_send_email');
            $table->dropColumn('invoice_auto_send_email_password');
        });
    }
}
