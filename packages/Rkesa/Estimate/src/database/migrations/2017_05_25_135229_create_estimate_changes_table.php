<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('estimate_changes')) {
            Schema::create('estimate_changes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('estimate_id');
                $table->integer('user_id');
                $table->timestamps();
                // Indexes
                $table->index('user_id');
                $table->index('estimate_id');
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
        Schema::dropIfExists('estimate_changes');
    }
}
