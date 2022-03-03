<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectNotification;

class AddUserIdToProjectNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_notification_recipients', function (Blueprint $table) {
            $table->integer('user_id')->nullable();
        });
        ProjectNotification::create(['type' => 'Order_sent_filled']);
        ProjectNotification::create(['type' => 'Order_confirmed_filled']);
        ProjectNotification::create(['type' => 'Designated_shipping_date_filled']);
        ProjectNotification::create(['type' => 'Fact_shipping_date_filled']);
        ProjectNotification::create(['type' => 'Date_of_application_filled']);
        ProjectNotification::create(['type' => 'Date_of_issue_filled']);
        ProjectNotification::create(['type' => 'Approximate_arrival_week_filled']); // renamed in following migration to Approximate_arrival_date_to_temporary_filled
        ProjectNotification::create(['type' => 'Approximate_arrival_date_filled']);
        ProjectNotification::create(['type' => 'Equipment_commissioning_certificate_filled']);
        ProjectNotification::create(['type' => 'Equipment_commissioning_experience_certificate_filled']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_notification_recipients', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
