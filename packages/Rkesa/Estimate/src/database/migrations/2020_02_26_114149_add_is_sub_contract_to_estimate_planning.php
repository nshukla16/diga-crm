<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSubContractToEstimatePlanning extends Migration
{
    public function up()
    {
        Schema::table('user_planning_user_tasks', function (Blueprint $table) {
            $table->boolean('is_subcontract')->nullable();
            $table->float('company_percent')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_planning_user_tasks', function (Blueprint $table) {
            $table->dropColumn('is_subcontract');
            $table->dropColumn('company_percent');
        });
    }
}
