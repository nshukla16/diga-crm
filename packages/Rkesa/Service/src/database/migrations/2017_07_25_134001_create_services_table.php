<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('client_id');
                $table->integer('responsible_user_id')->nullable();
                $table->integer('service_state_id');
                $table->integer('service_priority_id')->nullable();
                $table->integer('aru_id')->nullable(); // areas de reabilitacao discount
                $table->string('estimate_number');
                $table->double('estimate_summ')->nullable();
                $table->string('address')->nullable();
                $table->boolean('autocomplete_disabled');
                $table->integer('service_type_id')->nullable();
                $table->string('name')->nullable();
                $table->text('note')->nullable();
                $table->integer('master_estimate_id')->nullable();
                $table->integer('additional')->nullable();
                $table->double('paid_summ')->nullable();
                $table->integer('external_id')->nullable(); // WooCommerce for example
                $table->string('mongo_id'); // Remove in next release!
                $table->timestamps();
                // Indexes
                $table->index('client_id');
                $table->index(['client_id', 'estimate_number']);
                $table->index('responsible_user_id');
                $table->index('service_state_id');
                $table->index('service_priority_id');
                $table->index('service_type_id');
                $table->index(['id', 'estimate_number', 'address']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
