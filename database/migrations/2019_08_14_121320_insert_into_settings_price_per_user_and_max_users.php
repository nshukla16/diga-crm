<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertIntoSettingsPricePerUserAndMaxUsers extends Migration
{
    public function up()
    {
        $setting1 = new Setting;
        $setting1->key = "price_per_user";
        $setting1->value = "";
        $setting1->save();

        $setting2 = new Setting;
        $setting2->key = "max_users";
        $setting2->value = "1";
        $setting2->save();
    }

    public function down()
    {
        $setting1 = Setting::where('key', "price_per_user")->firstOrFail();
        $setting1->delete();

        $setting2 = Setting::where('key', "max_users")->firstOrFail();
        $setting2->delete();
    }
}
