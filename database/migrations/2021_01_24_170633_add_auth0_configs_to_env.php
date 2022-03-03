<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuth0ConfigsToEnv extends Migration
{
    use \App\Http\Traits\DotenvTrait;
    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "AUTH0_DOMAIN=diga.eu.auth0.com");
        self::dotenv_add_line(base_path('.env'), "AUTH0_CLIENT_ID=n2hhgGIZIjwoMxI8BOZeaU2bA2DJR4jM");
        self::dotenv_add_line(base_path('.env'), "AUTH0_CLIENT_SECRET=Bw6ZoRQdnhNZRnZld8RRABEJUkz8akq4AINplFLTSTYEQkFhktHralU0IaNUmi6w");
        self::dotenv_add_line(base_path('.env'), "API_IDENTIFIER=https://diga.pt");
    }

    public function down()
    {
        self::dotenv_remove_line_starting_with(base_path('.env'), 'AUTH0_DOMAIN=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'AUTH0_CLIENT_ID=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'AUTH0_CLIENT_SECRET=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'API_IDENTIFIER=');
    }
}
