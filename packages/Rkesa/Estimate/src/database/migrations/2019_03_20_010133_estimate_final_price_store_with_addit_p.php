<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Estimate\Models\Estimate;

class EstimateFinalPriceStoreWithAdditP extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $estimates = Estimate::all();
        foreach($estimates as $estimate){
            $estimate->price = $estimate->price * (1 + $estimate->additional_price/100);
            $estimate->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $estimates = Estimate::all();
        foreach($estimates as $estimate){
            $estimate->price = $estimate->price / (1 + $estimate->additional_price/100);
            $estimate->save();
        }
    }
}
