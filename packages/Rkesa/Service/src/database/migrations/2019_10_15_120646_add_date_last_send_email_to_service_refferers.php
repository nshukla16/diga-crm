<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateLastSendEmailToServiceRefferers extends Migration
{
    public function up()
    {
        Schema::table('service_referrers', function($table) {
            $table->timestamp('email_sent_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('service_referrers', function($table) {
            $table->dropColumn('email_sent_at');
        });
    }
}
