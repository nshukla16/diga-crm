<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\CommissionPayment;
use Rkesa\Project\Models\ManufacturerCommissionRelation;
use Rkesa\Project\Models\ProjectManufacturer;

class MoveCommissionStepsToCommissionRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commission_payments', function (Blueprint $table) {
            $table->integer('manufacturer_commission_relation_id');
        });
        $pms = ProjectManufacturer::all();
        foreach($pms as $pm){
            $mcr = new ManufacturerCommissionRelation;
            $mcr->legal_entity_id = $pm->project->commissioner_legal_entity_id;
            $mcr->project_manufacturer_id = $pm->id;
            $mcr->currency = $pm->project->contract_currency;
            $mcr->commission_need_to_pay = $pm->commission_need_to_pay;
            $mcr->comment_commission = $pm->comment_commission;
            $mcr->save();

            $cps = CommissionPayment::where('project_manufacturer_id', $pm->id)->get();
            foreach($cps as $cp){
                $cp->manufacturer_commission_relation_id = $mcr->id;
                $cp->save();
            }
        }
        Schema::table('commission_payments', function (Blueprint $table) {
            $table->dropColumn('project_manufacturer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commission_payments', function (Blueprint $table) {
            $table->dropColumn('manufacturer_commission_relation_id');
        });
        Schema::table('commission_payments', function (Blueprint $table) {
            $table->integer('project_manufacturer_id');
        });
    }
}
