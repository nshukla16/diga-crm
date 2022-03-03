<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUser extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position')->nullable();
            $table->integer('identical_type_id')->nullable();
            $table->date('identical_valid_to')->nullable();
            $table->integer('marital_status_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
            $table->dropColumn('identical_type_id');
            $table->dropColumn('identical_valid_to');
            $table->dropColumn('marital_status_id');
        });
    }
}
