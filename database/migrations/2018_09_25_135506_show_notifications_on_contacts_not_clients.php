<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShowNotificationsOnContactsNotClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['show_notification', 'show_fb_notification']);
        });
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->boolean('show_notification')->default(false);
            $table->boolean('show_fb_notification')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->dropColumn(['show_notification', 'show_fb_notification']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('show_notification')->default(false);
            $table->boolean('show_fb_notification')->default(false);
        });
    }
}
