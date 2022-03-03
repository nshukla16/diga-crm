<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableConnections extends Migration
{
    public function up()
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('url');
            $table->boolean('is_my');
            $table->boolean('is_subcontractor');
            $table->boolean('is_approved');
        });
    }

    public function down()
    {
        Schema::dropIfExists('connections');
    }
}
