<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentToTransportationPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transportation_payments', function (Blueprint $table) {
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
        Schema::table('transportation_payments', function (Blueprint $table) {
            $table->dropColumn(['document_file', 'document_file_name']);
        });
    }
}
