<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

include_once base_path('packages/Rkesa/Email/vendor/afterlogic_webmail/libraries/afterlogic/api.php');

class CreateWebmailTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (class_exists('CApi') && \CApi::IsValid()) {
            $oApiDbManager = \CApi::Manager('db');
            $bResult = $oApiDbManager->syncTables();
            if (!$bResult){
                throw new Exception($oApiDbManager->GetLastErrorMessage());
            }
        } else {
            throw new Exception('WebMail API isn\'t available: probably config file is incorrect');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // REMOVE WEBMAIL TABLES
    }
}
