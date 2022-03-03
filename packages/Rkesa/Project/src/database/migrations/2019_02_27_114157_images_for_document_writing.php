<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImagesForDocumentWriting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('legal_entities', function (Blueprint $table) {
            $table->string('logo_file_name')->nullable();
            $table->string('logo_file_path')->nullable();
            $table->string('sign_file_name')->nullable();
            $table->string('sign_file_path')->nullable();
        });

    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('legal_entities', function (Blueprint $table) {
            $table->dropColumn(['logo_file_name', 'logo_file_path', 'sign_file_name', 'sign_file_path']);
        });
    }
}
