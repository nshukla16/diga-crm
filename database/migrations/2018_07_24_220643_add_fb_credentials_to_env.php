<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFbCredentialsToEnv extends Migration
{
    use \App\Http\Traits\DotenvTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "FACEBOOK_CLIENT_ID={FACEBOOK_CLIENT_ID}");
        self::dotenv_add_line(base_path('.env'), "FACEBOOK_CLIENT_SECRET={FACEBOOK_CLIENT_SECRET}");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dotenv_remove_line_starting_with(base_path('.env'), 'FACEBOOK_CLIENT_ID=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'FACEBOOK_CLIENT_SECRET=');
    }

}
