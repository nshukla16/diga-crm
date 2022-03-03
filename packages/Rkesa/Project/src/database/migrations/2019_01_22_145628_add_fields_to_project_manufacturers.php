<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToProjectManufacturers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->boolean('invoice')->default(false);
            $table->dateTimeTz('invoice_date')->nullable();
            $table->string('invoice_file_path')->nullable();
            $table->string('invoice_file_name')->nullable();
            //
            $table->boolean('packing_list')->default(false);
            $table->dateTimeTz('packing_list_date')->nullable();
            $table->string('packing_list_file_path')->nullable();
            $table->string('packing_list_file_name')->nullable();
            //
            $table->boolean('photo')->default(false);
            $table->dateTimeTz('photo_date')->nullable();
            $table->string('photo_file_path')->nullable();
            $table->string('photo_file_name')->nullable();
            //
            $table->boolean('export_declaration')->default(false);
            $table->dateTimeTz('export_declaration_date')->nullable();
            $table->string('export_declaration_file_path')->nullable();
            $table->string('export_declaration_file_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->dropColumn([
                'invoice','invoice_date','invoice_file_path','invoice_file_name',
                'packing_list','packing_list_date','packing_list_file_path','packing_list_file_name',
                'photo','photo_date','photo_file_path','photo_file_name',
                'export_declaration','export_declaration_date','export_declaration_file_path','export_declaration_file_name'
            ]);
        });
    }
}
