<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransportationVatTotalToManufacturerOrders extends Migration
{
    public function up()
    {
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->double('transportation_vat_total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->dropColumn('transportation_vat_total');
        });
    }
}
