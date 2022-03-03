<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeStampsToCompanyInformation extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('company_information', 'updated_at'))
        {
            Schema::table('company_information', function (Blueprint $table)
            {
                $table->timestamps();
            });
        }
    }

    public function down()
    {
    }
}
