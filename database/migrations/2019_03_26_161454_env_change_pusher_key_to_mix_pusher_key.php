<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EnvChangePusherKeyToMixPusherKey extends Migration
{
    use \App\Http\Traits\DotenvTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::dotenv_change_line(base_path('.env'), 'PUSHER_KEY', 'MIX_PUSHER_KEY');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dotenv_change_line(base_path('.env'), 'MIX_PUSHER_KEY', 'PUSHER_KEY');
    }
}
