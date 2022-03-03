<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserContracts extends Migration
{
    public function up()
    {
        Schema::create('user_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id');

            $table->date('begin')->nullable();
            $table->date('end')->nullable();         

            $table->date('first_renovation_begin')->nullable();
            $table->date('first_renovation_end')->nullable(); 

            $table->date('second_renovation_begin')->nullable();
            $table->date('second_renovation_end')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_contracts');
    }
}
