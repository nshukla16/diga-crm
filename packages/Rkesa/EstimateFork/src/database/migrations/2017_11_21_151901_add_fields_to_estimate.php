<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToEstimate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('estimates')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->integer('fork_origin_estimate_id')->nullable();
                $table->integer('fork_id')->nullable();
                //
                $table->index('fork_origin_estimate_id');
                $table->index('fork_id');
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
        if (Schema::hasTable('estimates')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->dropIndex('estimates_fork_origin_estimate_id_index');
                $table->dropIndex('estimates_fork_id_index');
                $table->dropColumn(['fork_origin_estimate_id', 'fork_id']);
            });
        }
    }
}
