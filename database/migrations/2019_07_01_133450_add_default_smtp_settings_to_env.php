<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultSmtpSettingsToEnv extends Migration
{
    use \App\Http\Traits\DotenvTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "DEFAULT_MAIL_DRIVER=".env('SAAS_DEFAULT_MAIL_DRIVER', 'smtp'));
        self::dotenv_add_line(base_path('.env'), "DEFAULT_MAIL_HOST=".env('SAAS_DEFAULT_MAIL_HOST'));
        self::dotenv_add_line(base_path('.env'), "DEFAULT_MAIL_PORT=".env('SAAS_DEFAULT_MAIL_PORT'));
        self::dotenv_add_line(base_path('.env'), "DEFAULT_MAIL_USERNAME=".env('SAAS_DEFAULT_MAIL_USERNAME'));
        self::dotenv_add_line(base_path('.env'), "DEFAULT_MAIL_PASSWORD=".env('SAAS_DEFAULT_MAIL_PASSWORD'));
        self::dotenv_add_line(base_path('.env'), "DEFAULT_MAIL_ENCRYPTION=".env('SAAS_DEFAULT_MAIL_ENCRYPTION'));
        self::dotenv_reload(base_path());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dotenv_remove_line_starting_with(base_path('.env'), 'DEFAULT_MAIL_DRIVER=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'DEFAULT_MAIL_HOST=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'DEFAULT_MAIL_PORT=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'DEFAULT_MAIL_USERNAME=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'DEFAULT_MAIL_PASSWORD=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'DEFAULT_MAIL_ENCRYPTION=');
    }
}
