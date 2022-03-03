<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServiceReferals extends Migration
{
    public function up()
    {
        Schema::create('service_referrers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->nullable();

            $table->string('email');
            $table->string('password');
            $table->string('name');
            $table->text('hash')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `' . env('DB_DATABASE', '') . '`.service_referrers AUTO_INCREMENT = 20000;');
    }

    public function down()
    {
        Schema::drop('service_referrers');
    }
}
