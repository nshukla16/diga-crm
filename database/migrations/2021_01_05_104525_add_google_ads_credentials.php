<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleAdsCredentials extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('google_ads_enabled')->default(false);
            $table->string('google_ads_service_file_name')->nullable();
            $table->string('google_ads_service_file')->nullable();
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('google_ads_enabled');
            $table->dropColumn('google_ads_service_file');
            $table->dropColumn('google_ads_service_file_name');
        });
    }
}
