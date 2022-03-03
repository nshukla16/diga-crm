<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SoftDeletingEstimatePayStages extends Migration
{
    public function up()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
