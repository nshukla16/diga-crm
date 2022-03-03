<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToCompanyInformation extends Migration
{
    public function up()
    {
        Schema::table('company_information', function (Blueprint $table) {
            $table->string('crc');
            $table->string('crc_number');
        });

        Schema::table('global_settings', function (Blueprint $table) {
            $table->text('invoice_notes');
        });
    }

    public function down()
    {
        Schema::table('company_information', function (Blueprint $table) {
            $table->dropColumn('crc');
            $table->dropColumn('crc_number');
        });
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('invoice_notes');
        });
    }
}
