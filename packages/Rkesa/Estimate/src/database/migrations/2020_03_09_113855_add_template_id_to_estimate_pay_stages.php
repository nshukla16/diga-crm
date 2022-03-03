<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTemplateIdToEstimatePayStages extends Migration
{
    public function up()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->integer('email_template_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->dropColumn('email_template_id');
        });
    }
}
