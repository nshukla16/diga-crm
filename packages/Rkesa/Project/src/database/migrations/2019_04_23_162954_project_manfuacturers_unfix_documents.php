<?php

use App\GlobalSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\ProjectManufacturer;
use Rkesa\Project\Models\ProjectManufacturerAdditionalDocument;

class ProjectManfuacturersUnfixDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pms = ProjectManufacturer::all();
        $gs = GlobalSettings::first();
        foreach($pms as $pm){
            if ($pm->invoice){
                $tmp = new ProjectManufacturerAdditionalDocument;
                $tmp->document_name = trans('project.Invoice', [], $gs->default_language);
                $tmp->project_manufacturer_id = $pm->id;
                $tmp->exist = $pm->invoice;
                $tmp->document_date = $pm->invoice_date;
                $tmp->document_file = $pm->invoice_file_path;
                $tmp->document_file_name = $pm->invoice_file_name;
                $tmp->save();
            }
            if ($pm->packing_list){
                $tmp = new ProjectManufacturerAdditionalDocument;
                $tmp->document_name = trans('project.Packing_list', [], $gs->default_language);
                $tmp->project_manufacturer_id = $pm->id;
                $tmp->exist = $pm->packing_list;
                $tmp->document_date = $pm->packing_list_date;
                $tmp->document_file = $pm->packing_list_file_path;
                $tmp->document_file_name = $pm->packing_list_file_name;
                $tmp->save();
            }
            if ($pm->photo){
                $tmp = new ProjectManufacturerAdditionalDocument;
                $tmp->document_name = trans('project.Photo', [], $gs->default_language);
                $tmp->project_manufacturer_id = $pm->id;
                $tmp->exist = $pm->photo;
                $tmp->document_date = $pm->photo_date;
                $tmp->document_file = $pm->photo_file_path;
                $tmp->document_file_name = $pm->photo_file_name;
                $tmp->save();
            }
            if ($pm->export_declaration){
                $tmp = new ProjectManufacturerAdditionalDocument;
                $tmp->document_name = trans('project.Export_declaration', [], $gs->default_language);
                $tmp->project_manufacturer_id = $pm->id;
                $tmp->exist = $pm->export_declaration;
                $tmp->document_date = $pm->export_declaration_date;
                $tmp->document_file = $pm->export_declaration_file_path;
                $tmp->document_file_name = $pm->export_declaration_file_name;
                $tmp->save();
            }
        }
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->dropColumn([
                'invoice',
                'invoice_date',
                'invoice_file_path',
                'invoice_file_name',
                //
                'packing_list',
                'packing_list_date',
                'packing_list_file_path',
                'packing_list_file_name',
                //
                'photo',
                'photo_date',
                'photo_file_path',
                'photo_file_name',
                //
                'export_declaration',
                'export_declaration_date',
                'export_declaration_file_path',
                'export_declaration_file_name'
            ]);
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
}
