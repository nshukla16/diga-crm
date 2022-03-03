<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncrementToSeries extends Migration
{
    public function up()
    {
        Schema::table('invoice_series', function (Blueprint $table) {
            $table->integer('increment')->default(1);
        });
    }

    public function down()
    {
        Schema::table('invoice_series', function (Blueprint $table) {
            $table->dropColumn('increment');
        });
    }
}
