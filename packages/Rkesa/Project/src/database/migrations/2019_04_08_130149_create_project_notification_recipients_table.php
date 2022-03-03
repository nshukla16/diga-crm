<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectNotificationRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_notification_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('type');
            $table->integer('group_id')->nullable();
            $table->integer('project_notification_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_notification_recipients');
    }
}
