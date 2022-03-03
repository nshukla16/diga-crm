<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('global_settings')) {
            Schema::create('global_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('site_name');
                $table->string('site_logo');
                $table->string('site_logo_2');
                $table->integer('last_estimate_number');
                $table->integer('unlocker_user_id');
                $table->integer('responsible_user_id');
                $table->integer('company_type');
                $table->text('estimate_bottom_text')->nullable();
                $table->text('estimate_conditions_text')->nullable();
                $table->boolean('estimate_show_contract')->default(false);
                $table->integer('new_service_state_id');
                $table->integer('add_service_state_id');
                $table->integer('new_event_type_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('global_settings');
    }
}
