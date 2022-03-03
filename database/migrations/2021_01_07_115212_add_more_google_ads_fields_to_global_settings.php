<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreGoogleAdsFieldsToGlobalSettings extends Migration
{
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('google_ads_service_file');
            $table->dropColumn('google_ads_service_file_name');

            $table->string('google_ads_developer_token')->nullable();
            $table->string('google_ads_client_customer_id')->nullable();
            $table->string('google_ads_user_agent')->nullable();
            $table->string('google_ads_client_id')->nullable();
            $table->string('google_ads_client_secret')->nullable();
            $table->string('google_ads_refresh_token')->nullable();
        });
    }

    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('google_ads_service_file_name')->nullable();
            $table->string('google_ads_service_file')->nullable();

            $table->dropColumn('google_ads_developer_token');
            $table->dropColumn('google_ads_client_customer_id');
            $table->dropColumn('google_ads_user_agent');
            $table->dropColumn('google_ads_client_id');
            $table->dropColumn('google_ads_client_secret');
            $table->dropColumn('google_ads_refresh_token');
        });
    }
}
