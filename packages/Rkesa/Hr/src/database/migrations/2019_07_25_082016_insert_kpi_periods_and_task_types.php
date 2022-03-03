<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Hr\Models\KpiPeriod;
use Rkesa\Hr\Models\KpiType;

class InsertKpiPeriodsAndTaskTypes extends Migration
{
    public function up()
    {
        $period = new KpiPeriod;
        $period->name = "week";
        $period->save();

        $period1 = new KpiPeriod;
        $period1->name = "two_weeks";
        $period1->save();

        $period2 = new KpiPeriod;
        $period2->name = "month";
        $period2->save();

        $period3 = new KpiPeriod;
        $period3->name = "quarter";
        $period3->save();

        $period4 = new KpiPeriod;
        $period4->name = "year";
        $period4->save();

        $type = new KpiType;
        $type->name = "number_of_finished_tasks_any_type";
        $type->save();

        $type1 = new KpiType;
        $type1->name = "number_of_finished_tasks_of_special_type";
        $type1->save();

        $type2 = new KpiType;
        $type2->name = "number_of_incoming_calls";
        $type2->save();

        $type3 = new KpiType;
        $type3->name = "number_of_outgoing_calls";
        $type3->save();

        $type3 = new KpiType;
        $type3->name = "number_of_switching_statuses_of_services";
        $type3->save();

        $type4 = new KpiType;
        $type4->name = "number_of_sended_emails";
        $type4->save();

        $type5 = new KpiType;
        $type5->name = "number_of_working_time";
        $type5->save();
    }

    public function down()
    {
        KpiPeriod::truncate();
    }
}
