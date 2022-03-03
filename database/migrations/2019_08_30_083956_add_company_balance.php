<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyBalance extends Migration
{
    public function up()
    {
        Schema::create('balance_history', function (Blueprint $table) {
            $table->increments('id');
            $table->double('transfer_amount');
            $table->double('amount_before');
            $table->double('amount_after');
            $table->string('title');
            $table->timestamps();
        });

        $setting1 = new Setting;
        $setting1->key = "company_balance";
        $setting1->value = 0;
        $setting1->save();
    }

    public function down()
    {
        Schema::drop('balance_history');

        $setting2 = Setting::where('key', "company_balance")->firstOrFail();
        $setting2->delete();
    }
}
