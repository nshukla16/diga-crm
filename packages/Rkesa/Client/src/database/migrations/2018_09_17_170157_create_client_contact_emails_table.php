<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientContactEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_contact_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->integer('client_contact_id');
            $table->timestamps();
            // Indexes
            $table->index('email');
            $table->index('client_contact_id');
            $table->index(['client_contact_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_contact_emails');
    }
}
