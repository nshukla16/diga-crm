<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CopyDefaultSmtpSettingsToCurrent extends Migration
{
    use \App\Http\Traits\DotenvTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::dotenv_change_value(base_path('.env'), "MAIL_DRIVER", env('DEFAULT_MAIL_DRIVER'));
        self::dotenv_change_value(base_path('.env'), "MAIL_HOST", env('DEFAULT_MAIL_HOST'));
        self::dotenv_change_value(base_path('.env'), "MAIL_PORT", env('DEFAULT_MAIL_PORT'));
        self::dotenv_change_value(base_path('.env'), "MAIL_USERNAME", env('DEFAULT_MAIL_USERNAME'));
        self::dotenv_change_value(base_path('.env'), "MAIL_PASSWORD", env('DEFAULT_MAIL_PASSWORD'));
        self::dotenv_change_value(base_path('.env'), "MAIL_ENCRYPTION", env('DEFAULT_MAIL_ENCRYPTION'));
        self::dotenv_reload(base_path());
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
