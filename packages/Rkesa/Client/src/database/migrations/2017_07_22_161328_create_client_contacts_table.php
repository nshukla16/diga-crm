<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('client_contacts')) {
            Schema::create('client_contacts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('surname');
                $table->boolean('sex');
                $table->string('email');
                $table->string('profession');
                $table->string('education');
                $table->text('note');
                $table->string('nif');
                $table->integer('company_id');
                $table->integer('client_id');
                $table->integer('contact_type');
                $table->boolean('is_main_contact');
                $table->timestamps();
                //Indexes
                $table->index('company_id');
                $table->index('client_id');
                $table->index(array('name', 'surname', 'email'));
                $table->index(array('client_id', 'name', 'surname', 'email'));
                $table->index('email');
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
        Schema::dropIfExists('client_contacts');
    }
}
