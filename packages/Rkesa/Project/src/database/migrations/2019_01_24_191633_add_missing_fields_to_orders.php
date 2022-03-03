<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingFieldsToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->string('order_contract_and_specifications');
            $table->string('inner_specification_number');
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
            $table->dropColumn(['order_contract_and_specifications', 'inner_specification_number']);
        });
    }
}
