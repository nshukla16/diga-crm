<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\CalendarExtended\Seeds\EventGroupsSeeder;

class CreateEventGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('color');
        });
        $gs = \App\GlobalSettings::first();
        if ($gs) {
            $seeder = new EventGroupsSeeder;
            $seeder->run($gs->default_language);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_groups');
    }
}
