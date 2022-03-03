<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecklistFilledsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_filleds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('checklist_id');
            $table->integer('service_id');
            $table->text('note');
            $table->timestamps();
            // Indexes
            $table->index('checklist_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checklist_filleds');
    }
}
