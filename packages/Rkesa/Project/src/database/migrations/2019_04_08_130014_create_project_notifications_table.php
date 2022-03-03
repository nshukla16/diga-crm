<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectNotification;

class CreateProjectNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('type');
        });
        ProjectNotification::create(['type' => 'Prepayment_filled']);
        ProjectNotification::create(['type' => 'Payment_filled']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_notifications');
    }
}
