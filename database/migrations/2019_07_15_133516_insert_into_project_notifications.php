<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectNotification;

class InsertIntoProjectNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $notification = new ProjectNotification;
        $notification->type = "Manufacturer_order_created";
        $notification->save();

        $notification1 = new ProjectNotification;
        $notification1->type = "Manufacturer_bill_filled";
        $notification1->save();

        $notification2 = new ProjectNotification;
        $notification2->type = "Manufacturer_confirmed_filled";
        $notification2->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $notification = ProjectNotification::where('type', "Manufacturer_order_created")->firstOrFail();
        $notification->delete();
        $notification1 = ProjectNotification::where('type', "Manufacturer_bill_filled")->firstOrFail();
        $notification1->delete();
        $notification2 = ProjectNotification::where('type', "Manufacturer_confirmed_filled")->firstOrFail();
        $notification2->delete();
    }
}
