<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInnerDocsToProjectManufacturers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->integer('inner_seller_legal_entity_id')->nullable();
            $table->integer('inner_buyer_legal_entity_id')->nullable();
            $table->integer('buyer_legal_entity_id')->nullable();
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
            $table->dropColumn(['inner_seller_legal_entity_id', 'inner_buyer_legal_entity_id', 'buyer_legal_entity_id']);
        });
    }
}
