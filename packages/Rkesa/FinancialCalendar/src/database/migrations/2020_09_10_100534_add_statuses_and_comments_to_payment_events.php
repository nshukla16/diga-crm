<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusesAndCommentsToPaymentEvents extends Migration
{
    public function up()
    {
        Schema::table('payment_events', function (Blueprint $table) {
            $table->integer('status_id')->nullable();
            $table->integer('service_id')->nullable();
            $table->text('comment')->nullable();
        });
    }

    public function down()
    {
        Schema::table('payment_events', function (Blueprint $table) {
            $table->dropColumn('status_id');
            $table->dropColumn('comment');
            $table->dropColumn('service_id');
        });
    }
}
