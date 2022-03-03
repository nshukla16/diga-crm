<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_materials', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('resource_id');
            $table->integer('estimate_id');
            $table->double('quantity');
            $table->double('price');
            $table->timestamps();
            // Indexes
            $table->index('resource_id');
            $table->index('estimate_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_materials');
    }
}
