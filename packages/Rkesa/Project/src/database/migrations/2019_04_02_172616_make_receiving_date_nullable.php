<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\TechnicalDocument;

class MakeReceivingDateNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technical_documents', function (Blueprint $table) {
            $table->dateTimeTz('receiving_date')->nullable()->change();
        });
        $docs = TechnicalDocument::all();
        foreach($docs as $doc){
            if ($doc->receiving_date == '0000-00-00 00:00:00'){
                $doc->receiving_date = null;
                $doc->save();
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
        Schema::table('technical_documents', function (Blueprint $table) {
            $table->dateTimeTz('receiving_date')->nullable(false)->change();
        });
    }
}
