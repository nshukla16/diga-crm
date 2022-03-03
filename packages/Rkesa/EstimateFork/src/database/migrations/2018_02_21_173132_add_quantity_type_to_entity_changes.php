<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuantityTypeToEntityChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entity_changes', function (Blueprint $table) {
            $table->integer('quantity_type')->nullable(); // 1 - fixed, 2 - from another
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
            $table->dropColumn(['quantity_type']);
        });
    }
}
