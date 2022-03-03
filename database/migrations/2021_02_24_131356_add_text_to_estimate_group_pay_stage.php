<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTextToEstimateGroupPayStage extends Migration
{
    public function up()
    {
        Schema::table('estimate_group_pay_stages', function (Blueprint $table) {
            $table->string('text')->nullable();
            $table->date('payment_date')->nullable();
            $table->float('percent')->nullable();
            $table->integer('pay_stage_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('estimate_group_pay_stages', function (Blueprint $table) {
            $table->dropColumn('text');
            $table->dropColumn('payment_date');
            $table->dropColumn('percent');
            $table->integer('pay_stage_id')->change();
        });
    }
}
