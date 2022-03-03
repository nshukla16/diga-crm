<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('client_history')) {
            Schema::create('client_history', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('service_id')->nullable();
                $table->integer('service_state_id')->nullable();
                $table->integer('user_id');
                $table->integer('type_id');
                $table->integer('client_id');
                $table->integer('event_id')->nullable();
                $table->integer('site_id')->nullable();
                $table->integer('service_attachment_id')->nullable();
                $table->mediumText('message')->nullable();
                // TODO: make sure that message column is utf8mb4
                $table->timestamps();
                // Indexes
                $table->index('service_id');
                $table->index('service_state_id');
                $table->index('user_id');
                $table->index('client_id');
                $table->index('event_id');
                $table->index('site_id');
                $table->index('service_attachment_id');
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
        Schema::dropIfExists('client_history');
    }
}
