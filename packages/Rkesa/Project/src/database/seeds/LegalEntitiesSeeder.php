<?php

namespace Rkesa\Project\Seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegalEntitiesSeeder extends Seeder {

    public function run($lang = 'ru')
    {
        switch ($lang){
            case 'pt':
                DB::table('legal_entities')->insert(array(
                    array('id' => '1', 'name' => 'Entidade legal №1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
            case 'ru':
                DB::table('legal_entities')->insert(array(
                    array('id' => '1', 'name' => 'Юридическое лицо №1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
            case 'en':
                DB::table('legal_entities')->insert(array(
                    array('id' => '1', 'name' => 'Legal entity №1', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
        }
    }
}