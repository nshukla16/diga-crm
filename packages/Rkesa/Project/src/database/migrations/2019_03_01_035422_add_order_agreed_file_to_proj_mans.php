<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderAgreedFileToProjMans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->string('order_agreed_file')->nullable();
            $table->string('order_agreed_file_name')->nullable();
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
            $table->dropColumn('order_agreed_file', 'order_agreed_file_name');
        });
    }
}
