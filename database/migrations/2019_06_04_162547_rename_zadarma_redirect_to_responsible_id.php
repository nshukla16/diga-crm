<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameZadarmaRedirectToResponsibleId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->renameColumn('zadarma_redirect_to_responsible_id', 'zadarma_redirect_to_responsible');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->renameColumn('zadarma_redirect_to_responsible', 'zadarma_redirect_to_responsible_id');
        });
    }
}
