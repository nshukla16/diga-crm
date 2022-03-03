<?php

use App\GlobalSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\Project;
use Rkesa\Project\Models\ProjectStatus;
use Rkesa\Project\Seeds\ProjectStatusesSeeder;

class CreateProjectStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
        });
        $gs = GlobalSettings::first();
        if ($gs) {
            $seeder = new ProjectStatusesSeeder;
            $seeder->run($gs->default_language);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_statuses');
    }
}
