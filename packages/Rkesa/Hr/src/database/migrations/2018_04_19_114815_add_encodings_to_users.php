<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEncodingsToUsers extends Migration
{
    use \App\Http\Traits\DotenvTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('photo_encodings')->nullable();
        });
        //
        self::dotenv_add_line(base_path('.env'), "ERP_FACE_RECOGNITION_URL=127.0.0.1:8888");
        self::dotenv_reload(base_path());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photo_encodings']);
        });
        self::dotenv_remove_line_starting_with(base_path('.env'), 'ERP_FACE_RECOGNITION_URL=');
    }
}
