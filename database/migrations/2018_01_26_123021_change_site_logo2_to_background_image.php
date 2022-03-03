<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSiteLogo2ToBackgroundImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->renameColumn('site_logo_2', 'background_image');
        });
        $gs = \App\GlobalSettings::first();
        if ($gs){
            $gs->background_image = '/img/background.png';
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
            $table->renameColumn('background_image', 'site_logo_2');
        });
    }
}
