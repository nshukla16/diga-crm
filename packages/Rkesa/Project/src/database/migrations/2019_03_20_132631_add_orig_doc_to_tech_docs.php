<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrigDocToTechDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technical_documents', function (Blueprint $table) {
            $table->string('orig_document_file')->nullable();
            $table->string('orig_document_file_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technical_documents', function (Blueprint $table) {
            $table->dropColumn(['orig_document_file', 'orig_document_file_name']);
        });
    }
}
