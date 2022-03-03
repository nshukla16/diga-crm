<?php

namespace Rkesa\Client\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AruSeeder extends Seeder {

    public function run()
    {
        DB::unprepared(file_get_contents(base_path().'/packages/Rkesa/Client/src/database/seeds/aru.sql'));
    }
}