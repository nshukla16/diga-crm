<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableDashboardWidgetsAddData extends Migration
{
    public function up()
    {
        Schema::table('dashboard_widgets', function (Blueprint $table) {
            $table->string('data');
        });
    }

    public function down()
    {
        Schema::table('dashboard_widgets', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
}
