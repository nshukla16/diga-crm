<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimates')) {
            Schema::create('estimates', function (Blueprint $table) {
                $table->increments('id');
                $table->string('subject')->nullable();
                $table->integer('revision')->nullable();
                $table->integer('option')->nullable();
                $table->double('price');
                $table->boolean('blocked')->default(false);
                $table->integer('deadline');
                $table->double('additional_price');
                $table->integer('discount');
                $table->integer('user_id');
                $table->integer('service_id');
                $table->integer('vat_type');
                $table->integer('vat_maodeobra');
                $table->integer('vat_material');
                $table->text('vat_text')->nullable();
                $table->integer('head_id');
                $table->integer('route_index'); // for gmaps in planning
                $table->string('mongo_id'); // Remove in next release!
                $table->timestamps();
                // Indexes
                $table->index('user_id');
                $table->index('head_id');
                $table->index('service_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimates');
    }
}
