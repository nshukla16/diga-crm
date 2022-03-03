<?php

use App\Role;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Actions
        // 0 - forbidden
        // 1 - if responsible
        // 2 - for group
        // 3 - allowed
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('action');
            $table->integer('create')->default(0);
            $table->integer('read')->default(0);
            $table->integer('update')->default(0);
            $table->integer('delete')->default(0);
            $table->integer('export')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
