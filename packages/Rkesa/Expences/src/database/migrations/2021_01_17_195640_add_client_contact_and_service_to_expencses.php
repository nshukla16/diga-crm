<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientContactAndServiceToExpencses extends Migration
{
    public function up()
    {
        Schema::table('expences', function (Blueprint $table) {
            $table->integer('service_id')->unsigned()->nullable();
            $table->integer('client_contact_id')->unsigned()->nullable();

            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('client_contact_id')->references('id')->on('client_contacts');
        });
    }

    public function down()
    {
        Schema::table('expences', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['client_contact_id']);

            $table->dropColumn('service_id');
            $table->dropColumn('client_contact_id');
        });
    }
}
