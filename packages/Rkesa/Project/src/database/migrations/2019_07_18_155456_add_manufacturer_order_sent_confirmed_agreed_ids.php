<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManufacturerOrderSentConfirmedAgreedIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->integer('order_sent_contract_id');
            $table->integer('order_agreed_contract_id');
            $table->integer('order_confirmed_contract_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->dropColumn(['order_sent_contract_id', 'order_agreed_contract_id', 'order_confirmed_contract_id']);
        });
    }
}
