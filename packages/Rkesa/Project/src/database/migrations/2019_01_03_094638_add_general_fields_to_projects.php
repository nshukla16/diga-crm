<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGeneralFieldsToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('contract_number');
            $table->string('contract_file');
            $table->integer('contract_type');
            $table->boolean('phased_deliveries');
            $table->string('specification_number');
            $table->string('specification_file');
            $table->integer('conditions_of_delivery');
            $table->integer('client_id');
            $table->string('lessee');
            $table->string('commissioner');
            $table->double('contract_price');
            $table->string('contract_currency');
            $table->integer('contract_currency_type');
            $table->string('destination');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['project_type_id']);
        });
    }
}
