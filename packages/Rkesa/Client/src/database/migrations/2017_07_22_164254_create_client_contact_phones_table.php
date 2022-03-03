<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientContactPhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('client_contact_phones')) {
            Schema::create('client_contact_phones', function (Blueprint $table) {
                $table->increments('id');
                $table->string('phone_number');
                $table->integer('client_contact_id');
                $table->timestamps();
                // Indexes
                $table->index('phone_number');
                $table->index('client_contact_id');
                $table->index(['client_contact_id', 'phone_number']);
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
        Schema::dropIfExists('client_contact_phones');
    }
}
