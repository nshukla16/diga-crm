<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\CustomsDocument;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\ProjectManufacturer;

class MoveCustomsProcessFromProjectsToMans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Customs documents
        Schema::table('customs_documents', function (Blueprint $table) {
            $table->integer('project_manufacturer_id');
        });
        $docs = CustomsDocument::all();
        foreach($docs as $doc){
            $first_man = ProjectManufacturer::where('project_id', $doc->project_id)->first();
            if ($first_man) {
                $doc->project_manufacturer_id = $first_man->id;
                $doc->save();
            }
        }
        Schema::table('customs_documents', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });

        // Customs process
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->boolean('customs_application');
            $table->dateTimeTz('customs_application_date')->nullable();
            $table->boolean('customs_issue');
            $table->dateTimeTz('customs_issue_date')->nullable();
            $table->string('dt');
            $table->string('dt_file')->nullable();
            $table->string('dt_file_name')->nullable();
            $table->integer('approximate_week_of_arrival');
            $table->dateTimeTz('approximate_date_of_arrival');
        });
        $projects = Project::all();
        foreach($projects as $project){
            $pm = ProjectManufacturer::where('project_id', $project->id)->first();
            if ($pm){
                $pm->customs_application = $project->customs_application;
                $pm->customs_application_date = $project->customs_application_date;
                $pm->customs_issue = $project->customs_issue;
                $pm->customs_issue_date = $project->customs_issue_date;
                $pm->dt = $project->dt;
                $pm->dt_file = $project->dt_file;
                $pm->dt_file_name = $project->dt_file_name;
                $pm->approximate_week_of_arrival = $project->approximate_week_of_arrival;
                $pm->approximate_date_of_arrival = $project->approximate_date_of_arrival;

                $pm->save();
            }
        }
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'customs_application',
                'customs_application_date',
                'customs_issue',
                'customs_issue_date',
                'dt',
                'dt_file',
                'dt_file_name',
                'approximate_week_of_arrival',
                'approximate_date_of_arrival'
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
        // Sorry no time for this :(
    }
}
