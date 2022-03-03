<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ManufacturerOrder;
use Rkesa\Project\Models\TransportationPayment;

class MoveTransportationPaymentsFromProjToShipOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transportation_payments', function (Blueprint $table) {
            $table->integer('manufacturer_order_id');
        });
        $payments = TransportationPayment::all();
        foreach($payments as $payment){
            $mo = ManufacturerOrder::where('project_id', $payment->project_id)->first();
            if ($mo){
                $payment->manufacturer_order_id = $mo->id;
                $payment->save();
            }
        }
        Schema::table('transportation_payments', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
        // Add transportation total
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->double('transportation_total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transportation_payments', function (Blueprint $table) {
            $table->integer('project_id');
        });
        $payments = TransportationPayment::all();
        foreach($payments as $payment){
            $payment->project_id = ManufacturerOrder::find($payment->manufacturer_order_id)->project_id;
            $payment->save();
        }
        Schema::table('transportation_payments', function (Blueprint $table) {
            $table->dropColumn('manufacturer_order_id');
        });
        // Remove transportation total
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->dropColumn('transportation_total');
        });
    }
}
