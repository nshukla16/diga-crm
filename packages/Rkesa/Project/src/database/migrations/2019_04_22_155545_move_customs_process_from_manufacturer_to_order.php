<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Project\Models\CustomsDocument;
use Rkesa\Project\Models\ManufacturerOrder;
use Rkesa\Project\Models\ProjectManufacturer;

class MoveCustomsProcessFromManufacturerToOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Customs Documents
        Schema::table('customs_documents', function (Blueprint $table) {
            $table->integer('manufacturer_order_id');
        });
        $docs = CustomsDocument::all();
        foreach($docs as $doc){
            $doc->manufacturer_order_id = ProjectManufacturer::where('project_id', $doc->project_id)->first();
            $doc->save();
        }
        Schema::table('customs_documents', function (Blueprint $table) {
            $table->dropColumn('project_manufacturer_id');
        });

        // Customs Process
        Schema::table('manufacturer_orders', function (Blueprint $table) {
            $table->boolean('customs_application');
            $table->dateTimeTz('customs_application_date')->nullable();
            $table->boolean('customs_issue');
            $table->dateTimeTz('customs_issue_date')->nullable();
            $table->string('dt');
            $table->string('dt_file')->nullable();
            $table->string('dt_file_name')->nullable();
            $table->integer('approximate_week_of_arrival');
            $table->dateTimeTz('approximate_date_of_arrival');
        });
        $mans = ProjectManufacturer::all();
        foreach($mans as $man){
            $pm = ManufacturerOrder::where('project_manufacturer_id', $man->id)->first();
            if ($pm){
                $pm->customs_application = $man->customs_application;
                $pm->customs_application_date = $man->customs_application_date;
                $pm->customs_issue = $man->customs_issue;
                $pm->customs_issue_date = $man->customs_issue_date;
                $pm->dt = $man->dt;
                $pm->dt_file = $man->dt_file;
                $pm->dt_file_name = $man->dt_file_name;
                $pm->approximate_week_of_arrival = $man->approximate_week_of_arrival;
                $pm->approximate_date_of_arrival = $man->approximate_date_of_arrival;

                $pm->save();
            }
        }
        Schema::table('projects_manufacturers', function (Blueprint $table) {
            $table->dropColumn([
                'customs_application',
                'customs_application_date',
                'customs_issue',
                'customs_issue_date',
                'dt',
                'dt_file',
                'dt_file_name',
                'approximate_week_of_arrival',
                'approximate_date_of_arrival'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Sorry don't have time for this
    }
}
