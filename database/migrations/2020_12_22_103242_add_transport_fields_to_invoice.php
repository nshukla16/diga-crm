<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransportFieldsToInvoice extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('loading_address')->nullable();
            $table->string('loading_city')->nullable();
            $table->string('loading_postal_code')->nullable();
            $table->string('loading_country')->nullable();
            $table->date('loading_date')->nullable();

            $table->string('discharge_address')->nullable();
            $table->string('discharge_city')->nullable();
            $table->string('discharge_postal_code')->nullable();
            $table->string('discharge_country')->nullable();
            $table->string('discharge_registration')->nullable();
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('loading_address');
            $table->dropColumn('loading_city');
            $table->dropColumn('loading_postal_code');
            $table->dropColumn('loading_country');
            $table->dropColumn('loading_date');

            $table->dropColumn('discharge_address');
            $table->dropColumn('discharge_city');
            $table->dropColumn('discharge_postal_code');
            $table->dropColumn('discharge_country');
            $table->dropColumn('discharge_registration');
        });
    }
}
