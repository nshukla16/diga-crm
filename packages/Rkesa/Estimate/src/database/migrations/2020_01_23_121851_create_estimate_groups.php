<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstimateGroups extends Migration
{
    public function up()
    {
        Schema::create('estimate_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('estimate_id');
            $table->integer('group_id');
            $table->integer('percent');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estimate_groups');
    }
}
