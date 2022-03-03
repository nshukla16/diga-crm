<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Rkesa\Project\Models\ProjectAutotask;
use Illuminate\Database\Migrations\Migration;

class AddProjectAutotaskRow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $autotask = new ProjectAutotask;
        $autotask->type = "Technical_documentation_available";
        $autotask->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $autotask = ProjectAutotask::where('type', "Technical_documentation_available")->firstOrFail();
        $autotask->delete();
    }
}
