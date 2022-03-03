<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEventsSetNullable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTimeTz('finish')->nullable()->change();
            $table->integer('client_contact_id')->nullable()->change();            
        });
    }

    public function down()
    {
        Schema::table('estimate_group_workers', function (Blueprint $table) {
            $table->dateTimeTz('finish')->change();
            $table->integer('client_contact_id')->change();
        });
    }
}
