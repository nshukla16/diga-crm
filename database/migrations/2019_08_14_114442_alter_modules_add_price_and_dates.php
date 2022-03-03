<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterModulesAddPriceAndDates extends Migration
{
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->double('price')->nullable();
            $table->date('current_subscription_date_start')->nullable();
            $table->date('current_subscription_date_end')->nullable();
            $table->date('trial_date_start')->nullable();
            $table->date('trial_date_end')->nullable();
        });
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('current_subscription_date_start');
            $table->dropColumn('current_subscription_date_end');
            $table->dropColumn('trial_date_start');
            $table->dropColumn('trial_date_end');
        });
    }
}
