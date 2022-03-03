<?php

use App\GlobalSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientReferrer;

class FixExistedTestClientsWithReferrer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $gs = GlobalSettings::first();
        if ($gs){
            ClientContact::where(['id' => 1])->update(['client_referrer_id' => ClientReferrer::first()->id]);
            ClientContact::where(['id' => 2])->update(['client_referrer_id' => ClientReferrer::first()->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
