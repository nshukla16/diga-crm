<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertIntoSettingsMailchimp extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('mailchimp_integration_enabled')->default(false);
            $table->string('mailchimp_api_key');
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('mailchimp_integration_enabled');
            $table->dropColumn('mailchimp_api_key');
        });
    }
}
