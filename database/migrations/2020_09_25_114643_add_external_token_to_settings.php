<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExternalTokenToSettings extends Migration
{
    public function up()
    {
        $setting1 = new Setting;
        $setting1->key = "external_token";
        $setting1->value = "";
        $setting1->save();
    }

    public function down()
    {
        $setting1 = Setting::where('key', "external_token")->firstOrFail();
        $setting1->delete();
    }
}
