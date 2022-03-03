<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractNumberAndContractFileToContracts extends Migration
{
    public function up()
    {
        Schema::table('user_contracts', function (Blueprint $table) {
            $table->string('number')->nullable();
            $table->boolean('effective')->nullable();
            $table->text('contract_file')->nullable();
            $table->string('contract_file_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('user_contracts', function (Blueprint $table) {
            $table->dropColumn('number');
            $table->dropColumn('effective');
            $table->dropColumn('contract_file');
            $table->dropColumn('contract_file_name');
        });
    }
}
