<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPositionToEntityChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entity_changes', function (Blueprint $table) {
            $table->integer('position')->nullable(); // only for category/subcategory. 1 - top, 2 - bottom
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entity_changes', function (Blueprint $table) {
            $table->dropColumn(['position']);
        });
    }
}
