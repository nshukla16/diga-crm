<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManIdAndEqIdToSpecEquipments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('specification_equipments', function (Blueprint $table) {
            $table->integer('equipment_id');
            $table->integer('manufacturer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('specification_equipments', function (Blueprint $table) {
            $table->dropColumn(['equipment_id', 'manufacturer_id']);
        });
    }
}
