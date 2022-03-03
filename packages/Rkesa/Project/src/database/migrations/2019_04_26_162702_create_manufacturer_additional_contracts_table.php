<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManufacturerAdditionalContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacturer_additional_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('contract_number');
            $table->string('contract_file')->nullable();
            $table->string('contract_file_name')->nullable();
            $table->integer('project_manufacturer_id');
            $table->boolean('from_db');
            $table->integer('manufacturer_contract_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacturer_additional_contracts');
    }
}
