<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Rkesa\Project\Models\ProjectAutotask;
use Illuminate\Database\Migrations\Migration;

class AddSendInvoiceToPayToAutotasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $autotask = new ProjectAutotask;
        $autotask->type = "Invoice_confirmed";
        $autotask->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $autotask = ProjectAutotask::where('type', "Invoice_confirmed")->firstOrFail();
        $autotask->delete();
    }
}
