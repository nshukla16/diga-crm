<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyCredentials extends Migration
{
    public function up()
    {
        Schema::create('company_information', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->text('address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('phone');
            $table->string('fax');
            $table->string('email');            
            $table->string('web_site');
            $table->string('capital');
            $table->string('tax_number');
            $table->string('bank');
            $table->string('iban');
            $table->string('swift');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_information');
    }
}
