<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddZadarmaSettingsToGlobalSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('zadarma_enabled')->default(false);
            $table->string('zadarma_key');
            $table->string('zadarma_secret');
            $table->boolean('zadarma_redirect_to_responsible_id')->default(false);
            $table->boolean('zadarma_new_task_if_no_answer')->default(false);
            $table->integer('zadarma_task_type_id')->nullable()->default(1);
            $table->integer('zadarma_missed_call_responsible_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn([
                'zadarma_enabled', 
                'zadarma_key', 
                'zadarma_secret', 
                'zadarma_redirect_to_responsible_id',
                'zadarma_new_task_if_no_answer',
                'zadarma_task_type_id',
                'zadarma_missed_call_responsible_id'
            ]);
        });
    }
}
