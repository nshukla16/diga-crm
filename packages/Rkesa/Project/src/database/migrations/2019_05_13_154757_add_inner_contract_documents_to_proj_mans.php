<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInnerContractDocumentsToProjMans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->string('inner_contract_file')->nullable();
            $table->string('inner_contract_file_name')->nullable();
            $table->boolean('inner_contract_from_db');
            $table->integer('inner_contract_legal_entity_contract_id')->nullable();
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
                'inner_contract_file',
                'inner_contract_file_name',
                'inner_contract_from_db',
                'inner_contract_legal_entity_contract_id'
            ]);
        });
    }
}
