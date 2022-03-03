<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMainWebSiteToEnv extends Migration
{
    use \App\Http\Traits\DotenvTrait;
    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "MAIN_WEB_SITE=new.diga.pt");
    }

    public function down()
    {
        self::dotenv_remove_line_starting_with(base_path('.env'), 'MAIN_WEB_SITE=');
    }
}
