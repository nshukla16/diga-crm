<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('vip');
                $table->text('note');
                $table->integer('client_referrer_id');
                $table->boolean('show_notification')->default(false);
                $table->string('referrer_note');
                $table->string('mongo_id'); // Remove in next release!
                $table->timestamps();
                // Indexes
                $table->index('client_referrer_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
