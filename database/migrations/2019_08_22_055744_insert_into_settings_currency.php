<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertIntoSettingsCurrency extends Migration
{
    public function up()
    {
        $setting2 = new Setting;
        $setting2->key = "price_currency";
        $setting2->value = "";
        $setting2->save();
    }

    public function down()
    {
        $setting1 = Setting::where('key', "price_currency")->firstOrFail();
        $setting1->delete();
    }
}
