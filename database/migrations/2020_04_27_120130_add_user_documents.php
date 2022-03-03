<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserDocuments extends Migration
{
    public function up()
    {
        Schema::table('user_documents', function (Blueprint $table) {

            $table->dropColumn('name');
            $table->dropColumn('image');

            $table->string('type')->nullable();
            $table->text('file')->nullable();
            $table->string('file_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('file');
            $table->dropColumn('file_name');

            $table->string('name');
            $table->string('image');
        });
    }
}
