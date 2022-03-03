<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientAttributesToGs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->json('contact_attributes');
            $table->json('client_attributes');
        });
        $gs = \App\GlobalSettings::first();
        if ($gs){
            $gs->contact_attributes = [];
            $gs->client_attributes = [];
            $gs->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn(['contact_attributes', 'client_attributes']);
        });
    }
}
