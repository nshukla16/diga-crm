<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommissExpCertificateTemplateToLe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('legal_entities', function (Blueprint $table) {
            $table->string('commissioning_experience_certificate_template_file_path')->nullable();
            $table->string('commissioning_experience_certificate_template_file_name')->nullable();
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
            $table->dropColumn(['commissioning_experience_certificate_template_file_path','commissioning_experience_certificate_template_file_name']);
        });
    }
}
