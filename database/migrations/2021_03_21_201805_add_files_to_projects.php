<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFilesToProjects extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('calculation_of_direct_costs_file')->nullable();
            $table->string('calculation_of_direct_costs_file_name')->nullable();

            $table->string('commercial_offer_file')->nullable();
            $table->string('commercial_offer_file_name')->nullable();

            $table->string('offer_drawing_file')->nullable();
            $table->string('offer_drawing_file_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('calculation_of_direct_costs_file');
            $table->dropColumn('calculation_of_direct_costs_file_name');

            $table->dropColumn('commercial_offer_file');
            $table->dropColumn('commercial_offer_file_name');

            $table->dropColumn('offer_drawing_file');
            $table->dropColumn('offer_drawing_file_name');
        });
    }
}
