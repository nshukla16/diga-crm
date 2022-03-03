<?php

use App\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key');
            $table->string('value');
            // Indexes
            $table->index('key');
        });
        Setting::create(['key' => 'companies_show_checking_account', 'value' => '1']);
        Setting::create(['key' => 'companies_show_correspondent_account', 'value' => '1']);
        Setting::create(['key' => 'companies_show_nif', 'value' => '1']);
        Setting::create(['key' => 'companies_show_bic', 'value' => '1']);
        Setting::create(['key' => 'checklist_show_second_page', 'value' => '0']);
        Setting::create(['key' => 'show_aru', 'value' => '0']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
