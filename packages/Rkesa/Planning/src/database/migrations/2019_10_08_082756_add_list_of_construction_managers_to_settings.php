<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddListOfConstructionManagersToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /*
        Users in this list will receive a message when planning roadmap task is being updated
    */
    public function up()
    {
        Setting::create(['key' => 'construction_manager_list', 'value' => '']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where(['key' => 'construction_manager_list'])->delete();
    }
}
