<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('name');
            $table->string('address_legal');
            $table->string('address_mailing');
            $table->string('nif');
            $table->string('checking_account');
            $table->string('correspondent_account');
            $table->string('bic');
            $table->string('phone');
            $table->string('site');
            $table->string('email');
            $table->string('client_group');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['name', 'address_legal', 'address_mailing', 'nif', 'checking_account', 'correspondent_account', 'bic', 'phone', 'site', 'email', 'client_group']);
        });
    }
}
