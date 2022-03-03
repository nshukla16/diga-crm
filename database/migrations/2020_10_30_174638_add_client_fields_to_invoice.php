<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientFieldsToInvoice extends Migration
{
    public function up()
    {
        Schema::create('payment_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('days');
            $table->timestamps();
        });

        Schema::create('movement_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->integer('days');
            $table->timestamps();
        });

        Schema::create('vat_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('name');
            $table->double('percent');
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->double('gross_total_without_vat');
            $table->integer('client_id')->unsigned()->nullable();
            $table->integer('client_contact_id')->unsigned()->nullable();            
            $table->integer('service_id')->unsigned()->nullable();
            $table->integer('estimate_id')->unsigned()->nullable();
            $table->integer('pay_stage_id')->unsigned()->nullable();
            $table->integer('payment_condition_id')->unsigned()->nullable();
            $table->integer('movement_type_id')->unsigned()->nullable();

            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('code');
            $table->string('nif');
            $table->string('request');
            $table->string('currency');

            $table->double('exchange')->nullable();
            $table->double('desc_cli')->nullable();
            $table->double('desc_fin')->nullable();
            $table->date('maturity')->nullable();
            $table->double('postage')->nullable();
            $table->double('other_services')->nullable();
            $table->double('advances')->nullable();
            $table->double('settlement')->nullable();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('client_contact_id')->references('id')->on('client_contacts');
            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('estimate_id')->references('id')->on('estimates');
            $table->foreign('pay_stage_id')->references('id')->on('estimate_pay_stages');
            $table->foreign('payment_condition_id')->references('id')->on('payment_conditions');
            $table->foreign('movement_type_id')->references('id')->on('movement_types');
        });

        Schema::create('invoice_items', function (Blueprint $table) {

            $table->increments('id');
            $table->timestamps();

            $table->text('description');
            $table->double('quantity');
            $table->double('unit_price');
            $table->double('discount');
            $table->string('unit');            

            $table->integer('invoice_id')->unsigned();
            $table->integer('vat_type_id')->unsigned();            

            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->foreign('vat_type_id')->references('id')->on('vat_types');
        });
    }

    public function down()
    {      
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['client_id']); 
            $table->dropForeign(['client_contact_id']);
            $table->dropForeign(['service_id']);
            $table->dropForeign(['estimate_id']); 
            $table->dropForeign(['pay_stage_id']); 
            $table->dropForeign(['payment_condition_id']); 
            $table->dropForeign(['movement_type_id']);

            $table->dropColumn('client_id');
            $table->dropColumn('client_contact_id');
            $table->dropColumn('service_id');
            $table->dropColumn('estimate_id');
            $table->dropColumn('pay_stage_id');
            $table->dropColumn('payment_condition_id');
            $table->dropColumn('movement_type_id');
            $table->dropColumn('gross_total_without_vat');

            $table->dropColumn('name');
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('code');
            $table->dropColumn('nif');
            $table->dropColumn('request');
            $table->dropColumn('currency');

            $table->dropColumn('exchange')->nullable();
            $table->dropColumn('desc_cli')->nullable();
            $table->dropColumn('desc_fin')->nullable();
            $table->dropColumn('maturity')->nullable();
            $table->dropColumn('postage')->nullable();
            $table->dropColumn('other_services')->nullable();
            $table->dropColumn('advances')->nullable();
            $table->dropColumn('settlement')->nullable();
        });

        Schema::dropIfExists('invoice_items');

        Schema::dropIfExists('movement_types');
        Schema::dropIfExists('vat_types');        
        Schema::dropIfExists('payment_conditions');

    }
}
