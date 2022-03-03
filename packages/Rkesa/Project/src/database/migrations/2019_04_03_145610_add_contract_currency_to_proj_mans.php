<?php

use App\GlobalSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectManufacturer;

class AddContractCurrencyToProjMans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->string('contract_currency');
            $table->string('inner_contract_currency');
        });
        $gs = GlobalSettings::first();
        $mans = ProjectManufacturer::all();
        foreach($mans as $m){
            $m->contract_currency = $gs->currency;
            $m->inner_contract_currency = $gs->currency;
            $m->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->dropColumn(['contract_currency', 'inner_contract_currency']);
        });
    }
}
