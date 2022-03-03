<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\ProjectManufacturer;

class MoveWarrantyFieldsFromProjectsToMans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->smallInteger('warranty_period')->nullable();
            $table->dateTimeTz('warranty_expiration_date')->nullable();
            $table->dateTimeTz('initial_date')->nullable();
        });
        $projects = Project::all();
        foreach($projects as $project){
            $pm = ProjectManufacturer::where('project_id', $project->id)->first();
            if ($pm){
                $pm->warranty_period = $project->warranty_period;
                $pm->warranty_expiration_date = $project->warranty_expiration_date;
                $pm->initial_date = $project->initial_date;

                $pm->save();
            }
        }
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'warranty_period', 'warranty_expiration_date', 'initial_date'
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
                'warranty_period', 'warranty_expiration_date', 'initial_date'
            ]);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->smallInteger('warranty_period')->nullable();
            $table->dateTimeTz('warranty_expiration_date')->nullable();
            $table->dateTimeTz('initial_date')->nullable();
        });
    }
}
