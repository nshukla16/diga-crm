<?php

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectNotification;

class RenameApproximateWeekOfArrivalToArrivalToSvh extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->dateTimeTz('approximate_date_of_arrival_to_temporary')->nullable();
            $table->dropColumn('approximate_week_of_arrival');
        });

        $pn = ProjectNotification::where('type', 'Approximate_arrival_week_filled')->first();
        if ($pn){
            $pn->type = 'Approximate_arrival_date_to_temporary_filled';
            $pn->save();
        }
        $dns = DatabaseNotification::where('type', 'Rkesa\Project\Notifications\ProjectChanged')->get();
        foreach($dns as $dn){
            if ($dn->data['type'] == 'Approximate_arrival_week_filled'){
                $new_data = $dn->data;
                $new_data['type'] = 'Approximate_arrival_date_to_temporary_filled';
                $dn->data = $new_data;
                $dn->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->dropColumn('approximate_date_of_arrival_to_temporary');
            $table->integer('approximate_week_of_arrival');
        });
    }
}
