<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectNotification;

class MoveFactDeliveryDateFromProjToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->boolean('fact_delivery')->nullable(false);
            $table->dateTimeTz('fact_delivery_date')->nullable();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['fact_delivery', 'fact_delivery_date']);
        });
        ProjectNotification::create(['type' => 'Order_fact_delivery_filled']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->dropColumn(['fact_delivery', 'fact_delivery_date']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('fact_delivery')->nullable(false);
            $table->dateTimeTz('fact_delivery_date')->nullable();
        });
    }
}
