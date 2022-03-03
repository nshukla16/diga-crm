<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceToServiceTypes extends Migration
{
    public function up()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->float('price')->nullable();
        });
    }

    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn(['price']);
        });
    }
}
