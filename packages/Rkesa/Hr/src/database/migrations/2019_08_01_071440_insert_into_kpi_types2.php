<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Hr\Models\KpiType;

class InsertIntoKpiTypes2 extends Migration
{
    public function up()
    {
        $type3 = new KpiType;
        $type3->name = "number_of_created_contacts";
        $type3->save();

        $type3 = new KpiType;
        $type3->name = "number_of_created_companies";
        $type3->save();

        $type4 = new KpiType;
        $type4->name = "number_of_created_services";
        $type4->save();

        $type5 = new KpiType;
        $type5->name = "number_of_created_tasks_of_any_type";
        $type5->save();

        $type6 = new KpiType;
        $type6->name = "number_of_created_tasks_of_special_type";
        $type6->save();
    }

    public function down()
    {
    }
}
