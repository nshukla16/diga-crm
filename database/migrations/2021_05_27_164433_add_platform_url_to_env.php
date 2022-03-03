<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlatformUrlToEnv extends Migration
{
    use \App\Http\Traits\DotenvTrait;
    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "PLATFORM_URL=https://pec.pt");
        self::dotenv_add_line(base_path('.env'), "PLATFORM_API_IDENTIFIER=https://pec.pt");
        self::dotenv_add_line(base_path('.env'), "MACHINE_CLIENT_ID=t2wYZAzl3Zx8N1lF5FsUD5SL07CdPc5J");
        self::dotenv_add_line(base_path('.env'), "MACHINE_CLIENT_SECRET=TKfOwGxvXlIC39LV4nh1-RY1Irs6ySlwQFPiWojBdiAveOZdXN5UyhoGKgihIxgg");
    }

    public function down()
    {
        self::dotenv_remove_line_starting_with(base_path('.env'), 'PLATFORM_URL=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'PLATFORM_API_IDENTIFIER=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'MACHINE_CLIENT_ID=');
        self::dotenv_remove_line_starting_with(base_path('.env'), 'MACHINE_CLIENT_SECRET=');
    }
}
