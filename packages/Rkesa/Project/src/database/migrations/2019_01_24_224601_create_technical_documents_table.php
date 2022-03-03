<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_id');
            $table->string('name');
            $table->integer('days_from_prepayment_customer');
            $table->string('document_language');
            $table->integer('days_from_prepayment_manufacturer');
            $table->boolean('available');
            $table->dateTimeTz('receiving_date');
            $table->dateTimeTz('translating_date');
            $table->dateTimeTz('sending_date');
            $table->string('document_file')->nullable();
            $table->string('document_file_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('technical_documents');
    }
}
