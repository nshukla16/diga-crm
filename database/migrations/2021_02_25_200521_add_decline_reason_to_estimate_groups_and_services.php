<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeclineReasonToEstimateGroupsAndServices extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->text('contractor_decline_reason')->nullable();
        });
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->text('contractor_decline_reason')->nullable();
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('contractor_decline_reason');
        });
        Schema::table('estimate_groups', function (Blueprint $table) {
            $table->text('contractor_decline_reason')->nullable();
        });
    }
}
