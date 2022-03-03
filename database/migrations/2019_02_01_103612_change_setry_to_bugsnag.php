<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSetryToBugsnag extends Migration
{
    use \App\Http\Traits\DotenvTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "BUGSNAG_API_KEY=92d0feac13ee8d62238ccfb6101294e5");
        self::dotenv_remove_line_starting_with(base_path('.env'), 'SENTRY_LARAVEL_DSN=');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dotenv_add_line(base_path('.env'), "SENTRY_LARAVEL_DSN=https://645032ce4d7c4bc1b29313e74cc4c3d9@sentry.io/1375439");
        self::dotenv_remove_line_starting_with(base_path('.env'), 'BUGSNAG_API_KEY=');
    }
}
