<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSiteToReferrerInGlobalSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $gs = \App\GlobalSettings::first();
        if ($gs){
            $gs->incoming_sms_text = str_replace('{site}', '{referrer}', $gs->incoming_sms_text);
            $gs->incoming_mail_subject = str_replace('{site}', '{referrer}', $gs->incoming_mail_subject);
            $gs->incoming_mail_text = str_replace('{site}', '{referrer}', $gs->incoming_mail_text);
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
        $gs = \App\GlobalSettings::first();
        if ($gs){
            $gs->incoming_sms_text = str_replace('{referrer}', '{site}', $gs->incoming_sms_text);
            $gs->incoming_mail_subject = str_replace('{referrer}', '{site}', $gs->incoming_mail_subject);
            $gs->incoming_mail_text = str_replace('{referrer}', '{site}', $gs->incoming_mail_text);
            $gs->save();
        }
    }
}
