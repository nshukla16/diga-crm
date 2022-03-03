<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInnerFieldsToProjManufacturers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->string('inner_contract_number');
            $table->string('inner_specification_number'); // removed in following migration
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->dropColumn(['inner_contract_number', 'inner_specification_number']);
        });
    }
}
