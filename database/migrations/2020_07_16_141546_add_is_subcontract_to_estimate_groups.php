<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSubcontractToEstimateGroups extends Migration
{
    public function up()
    {
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->boolean('is_subcontract')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->dropColumn('is_subcontract');
        });
    }
}
