<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToServiceReferrer extends Migration
{
    public function up()
    {
        Schema::table('service_referrers', function($table) {
            $table->boolean('agree_to_receive_promotions')->nullable();
            $table->string('locale')->nullable();
        });
    }

    public function down()
    {
        Schema::table('service_referrers', function($table) {
            $table->dropColumn('agree_to_receive_promotions');
            $table->dropColumn('locale');
        });
    }
}
