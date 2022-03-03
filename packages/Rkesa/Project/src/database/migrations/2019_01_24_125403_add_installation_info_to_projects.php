<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstallationInfoToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->smallInteger('installation_duration')->nullable();
            $table->dateTimeTz('initial_date')->nullable();
            $table->boolean('equipment_certificate')->nullable();
            $table->dateTimeTz('equipment_certificate_date')->nullable();
            $table->string('equipment_certificate_file_path')->nullable();
            $table->string('equipment_certificate_file_name')->nullable();
            $table->boolean('equipment_ex_certificate')->nullable();
            $table->string('equipment_ex_certificate_file_name')->nullable();
            $table->string('equipment_ex_certificate_file_path')->nullable();
            $table->dateTimeTz('equipment_ex_certificate_date')->nullable();
            $table->smallInteger('warranty_period')->nullable();
            $table->dateTimeTz('warranty_expiration_date')->nullable();
            $table->text('payment_installation_comment')->nullable();
            $table->smallInteger('direct_expenses')->nullable();
            $table->smallInteger('transportation_expenses')->nullable();
            $table->smallInteger('airline_tickets_expenses')->nullable();
            $table->smallInteger('food_expenses')->nullable();
            $table->smallInteger('accommodation_expenses')->nullable();
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
            $table->dropColumn([
                "installation_duration",
                "initial_date",
                "equipment_certificate",
                "equipment_certificate_date",
                "equipment_certificate_file_path",
                "equipment_certificate_file_name",
                "equipment_ex_certificate",
                "equipment_ex_certificate_file_name",
                "equipment_ex_certificate_file_path",
                "equipment_ex_certificate_date",
                "warranty_period",
                "warranty_expiration_date",
                "payment_installation_comment",
                "direct_expenses",
                "transportation_expenses",
                "airline_tickets_expenses",
                "food_expenses",
                "accommodation_expenses"
                ]);
        });
    }
}
