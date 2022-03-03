<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectAutotask;

class CreateProjectAutotasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_autotasks', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('type');
        });
        ProjectAutotask::create(['type' => 'Shipping_date_filled']);
        ProjectAutotask::create(['type' => 'Inner_bill_filled']);
        ProjectAutotask::create(['type' => 'Inner_confirmed_filled']);
        ProjectAutotask::create(['type' => 'Manufacturer_bill_filled']);
        ProjectAutotask::create(['type' => 'Manufacturer_confirmed_filled']);
        ProjectAutotask::create(['type' => 'Dt_filled']);
        ProjectAutotask::create(['type' => 'Transportation_bill_filled']);
        ProjectAutotask::create(['type' => 'Transportation_confirmed_filled']);
        ProjectAutotask::create(['type' => 'Installation_bill_filled']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_autotasks');
    }
}
