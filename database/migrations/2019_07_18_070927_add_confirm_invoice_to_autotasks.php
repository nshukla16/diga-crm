<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Rkesa\Project\Models\ProjectAutotask;
use Illuminate\Database\Migrations\Migration;

class AddConfirmInvoiceToAutotasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $autotask = new ProjectAutotask;
        $autotask->type = "Invoice_uploaded";
        $autotask->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $autotask = ProjectAutotask::where('type', "Invoice_uploaded")->firstOrFail();
        $autotask->delete();
    }
}
