<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EstimatesAddCustomVat extends Migration
{
    public function up()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->integer('vat_custom')->nullable();
        });
    }

    public function down()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('vat_custom');
        });
    }
}