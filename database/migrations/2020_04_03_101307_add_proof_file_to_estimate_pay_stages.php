<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProofFileToEstimatePayStages extends Migration
{
    public function up()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->text('proof_file')->nullable();
            $table->string('proof_file_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_pay_stages', function (Blueprint $table) {
            $table->dropColumn('proof_file');
            $table->dropColumn('proof_file_name');
        });
    }
}
