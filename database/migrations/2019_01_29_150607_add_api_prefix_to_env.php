<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApiPrefixToEnv extends Migration
{
    use \App\Http\Traits\DotenvTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "API_PREFIX=api");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dotenv_remove_line_starting_with(base_path('.env'), 'API_PREFIX=');
    }
}
