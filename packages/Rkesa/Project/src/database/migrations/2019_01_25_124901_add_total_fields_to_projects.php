<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalFieldsToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->double('contract_total_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['contract_total_price']);
        });
    }
}