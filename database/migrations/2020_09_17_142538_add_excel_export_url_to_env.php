<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExcelExportUrlToEnv extends Migration
{
    use \App\Http\Traits\DotenvTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::dotenv_add_line(base_path('.env'), "EXCEL_EXPORT_ENDPOINT=127.0.0.1:8049");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dotenv_remove_line_starting_with(base_path('.env'), 'EXCEL_EXPORT_ENDPOINT=');
    }
}
