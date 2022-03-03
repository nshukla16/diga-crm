<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostalCodeToClientsAndContacts extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
        });

        Schema::table('client_contacts', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('postal_code');
            $table->dropColumn('city');
        });

        Schema::table('client_contacts', function (Blueprint $table) {
            $table->dropColumn('address');
            $table->dropColumn('postal_code');
            $table->dropColumn('city');
        });
    }
}
