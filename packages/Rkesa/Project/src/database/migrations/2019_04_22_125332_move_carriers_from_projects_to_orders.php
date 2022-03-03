<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectCarrier;
use Rkesa\Project\Models\ProjectManufacturer;

class MoveCarriersFromProjectsToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_carriers', function (Blueprint $table) {
            $table->integer('manufacturer_order_id');
        });
        $carriers = ProjectCarrier::all();
        foreach($carriers as $carrier){
            $carrier->manufacturer_order_id = ProjectManufacturer::where('project_id', $carrier->project_id)->first();
            $carrier->save();
        }
        Schema::table('project_carriers', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->text('comment_carrier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Sorry don't have time for this
    }
}
