<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->increments('id');
                $table->dateTimeTz('start');
                $table->dateTimeTz('finish');
                $table->integer('user_id');
                $table->integer('client_id');
                $table->integer('creator_user_id');
                $table->integer('event_type_id');
                $table->integer('service_id')->nullable();
                $table->text('description')->nullable();
                $table->boolean('done');
                $table->boolean('show_notification')->default(false);
                $table->timestamps();
                // Indexes
                $table->index('user_id');
                $table->index('service_id');
                $table->index('client_id');
                $table->index('event_type_id');
                $table->index('creator_user_id');
                $table->index('show_notification');
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
        Schema::dropIfExists('events');
    }
}
