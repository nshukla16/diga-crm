<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManufacturerOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacturer_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_id');
            // logical divider
            $table->dateTimeTz('order_date');
            $table->integer('responsible_user_id');
            $table->integer('sender_manufacturer_id');
            $table->string('sender_legal_address');
            $table->string('uploading_address');
            $table->integer('manufacturer_contact_id'); // not using
            $table->string('manufacturer_contact_phone');
            $table->string('manufacturer_contact_email');
            $table->dateTimeTz('loading_ready_date');
//            $table->('order_contract_and_specifications'); // in following migration
            $table->integer('conditions_of_delivery');
            $table->string('additional_loading');
            $table->string('shipment_place');
            //destination place in following migration
            $table->string('shipment_type_and_counts');
            $table->string('consignment_receiver_company_name');
            $table->string('consignment_receiver_address');
            $table->string('consignment_receiver_phone');
//            $table->integer('client_id');
            $table->string('client_contract_number');
            $table->string('downloading_address');
            $table->string('downloading_contact_phone');
            $table->integer('manufacturer_id');
            $table->integer('manufacturer_legal_address');
            $table->string('loading_name');
            $table->string('loading_size');
            $table->double('loading_selling_price');
            $table->double('loading_cost_price');
//            $table->string('inner_specification_number'); // in following migration
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacturer_orders');
    }
}
