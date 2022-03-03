<?php

use App\CompanyInformation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertItemToCompanyInformation extends Migration
{
    public function up()
    {
        $item = new CompanyInformation();
        $item->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $item = CompanyInformation::first();
        $item->delete();
    }
}
