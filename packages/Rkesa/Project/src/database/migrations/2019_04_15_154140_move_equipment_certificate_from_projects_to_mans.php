<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\ProjectManufacturer;

class MoveEquipmentCertificateFromProjectsToMans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->boolean('equipment_certificate')->nullable();
            $table->dateTimeTz('equipment_certificate_date')->nullable();
            $table->string('equipment_certificate_file_path')->nullable();
            $table->string('equipment_certificate_file_name')->nullable();
            $table->boolean('equipment_ex_certificate')->nullable();
            $table->string('equipment_ex_certificate_file_name')->nullable();
            $table->string('equipment_ex_certificate_file_path')->nullable();
            $table->dateTimeTz('equipment_ex_certificate_date')->nullable();
        });
        $projects = Project::all();
        foreach($projects as $project){
            $pm = ProjectManufacturer::where('project_id', $project->id)->first();
            if ($pm){
                $pm->equipment_certificate = $project->equipment_certificate;
                $pm->equipment_certificate_date = $project->equipment_certificate_date;
                $pm->equipment_certificate_file_path = $project->equipment_certificate_file_path;
                $pm->equipment_certificate_file_name = $project->equipment_certificate_file_name;

                $pm->equipment_ex_certificate = $project->equipment_ex_certificate;
                $pm->equipment_ex_certificate_file_name = $project->equipment_ex_certificate_file_name;
                $pm->equipment_ex_certificate_file_path = $project->equipment_ex_certificate_file_path;
                $pm->equipment_ex_certificate_date = $project->equipment_ex_certificate_date;
                $pm->save();
            }
        }
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'equipment_certificate',
                'equipment_certificate_date',
                'equipment_certificate_file_path',
                'equipment_certificate_file_name',
                'equipment_ex_certificate',
                'equipment_ex_certificate_file_name',
                'equipment_ex_certificate_file_path',
                'equipment_ex_certificate_date'
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
            $table->dropColumn([
                'equipment_certificate',
                'equipment_certificate_date',
                'equipment_certificate_file_path',
                'equipment_certificate_file_name',
                'equipment_ex_certificate',
                'equipment_ex_certificate_file_name',
                'equipment_ex_certificate_file_path',
                'equipment_ex_certificate_date'
            ]);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('equipment_certificate')->nullable();
            $table->dateTimeTz('equipment_certificate_date')->nullable();
            $table->string('equipment_certificate_file_path')->nullable();
            $table->string('equipment_certificate_file_name')->nullable();
            $table->boolean('equipment_ex_certificate')->nullable();
            $table->string('equipment_ex_certificate_file_name')->nullable();
            $table->string('equipment_ex_certificate_file_path')->nullable();
            $table->dateTimeTz('equipment_ex_certificate_date')->nullable();
        });
    }
}
