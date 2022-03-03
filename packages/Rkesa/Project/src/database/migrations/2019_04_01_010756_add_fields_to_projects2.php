<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\Project;
use App\User;

class AddFieldsToProjects2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('finished')->default(false);
            $table->integer('responsible_user_id');
        });
        // If we have any existing projects
        // TODO: remove in future
        $projects = Project::all();
        $user = User::where('deleted_at', null)->first();
        if ($user) {
            foreach ($projects as $project) {
                $project->responsible_user_id = $user->id;
                $project->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['finished', 'responsible_user_id']);
        });
    }
}
