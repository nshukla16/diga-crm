<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSomeIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimate_lines', function (Blueprint $table) {
            $table->index(['parent_id', 'order']);
        });
        Schema::table('estimate_line_ficha_resources', function (Blueprint $table) {
            $table->index(['resource_id', 'estimate_line_ficha_id'], 'resource_id_and_ficha_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estimate_lines', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'order']);
        });
        Schema::table('estimate_line_ficha_resources', function (Blueprint $table) {
            $table->dropIndex('resource_id_and_ficha_id');
        });
    }
}
