<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateGroupPayStages extends Migration
{
    public function up()
    {
        Schema::create('estimate_group_pay_stages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('estimate_group_id');
            $table->integer('pay_stage_id');
            $table->tinyInteger('paid');
            $table->string('invoice_number');
            $table->float('fact_paid');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estimate_group_pay_stages');
    }
}
