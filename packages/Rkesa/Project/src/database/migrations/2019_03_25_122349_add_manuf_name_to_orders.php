<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManufNameToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->string('manufacturer_name');
            $table->string('manufacturer_legal_address')->change();
        });
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->dropColumn('loading_size');
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
            $table->dropColumn('manufacturer_name');
            $table->integer('manufacturer_legal_address')->change();
        });
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->string('loading_size');
        });
    }
}
