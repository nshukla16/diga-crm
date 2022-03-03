<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManufacturerCommissionRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacturer_commission_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_manufacturer_id');
            $table->double('commission_need_to_pay');
            $table->string('currency');
            $table->text('comment_commission');
            $table->integer('legal_entity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacturer_commission_relations');
    }
}
