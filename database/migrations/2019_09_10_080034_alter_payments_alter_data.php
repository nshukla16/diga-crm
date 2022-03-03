<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPaymentsAlterData extends Migration
{
    public function up()
    {
        Schema::table('user_payments', function (Blueprint $table) {
            $table->text('data')->change();
        });
    }

    public function down()
    {
        //
    }
}
