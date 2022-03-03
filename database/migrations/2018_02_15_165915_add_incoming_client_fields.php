<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncomingClientFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('incoming_sms')->default(false);
            $table->text('incoming_sms_text');
            $table->boolean('incoming_mail')->default(false);
            $table->string('incoming_mail_subject')->default('New client!');
            $table->text('incoming_mail_text');
            $table->string('incoming_mail_address')->default('admin@example.com');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn(['incoming_sms', 'incoming_sms_text', 'incoming_mail', 'incoming_mail_subject', 'incoming_mail_text', 'incoming_mail_address']);
        });
    }
}
