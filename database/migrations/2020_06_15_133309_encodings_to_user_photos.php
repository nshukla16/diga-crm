<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EncodingsToUserPhotos extends Migration
{
    public function up()
    {
       Artisan::call('hr:get_photo_encodings');
    }

    public function down()
    {
    }
}
