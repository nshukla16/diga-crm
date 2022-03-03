<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectManufacturerAdditionalDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_manufacturer_additional_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('project_manufacturer_id');
            $table->string('document_name');
            $table->boolean('exist');
            $table->dateTimeTz('document_date')->nullable();
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
        Schema::dropIfExists('project_manufacturer_additional_documents');
    }
}
