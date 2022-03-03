<?php

namespace Rkesa\Project\Seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectStatusesSeeder extends Seeder {

    public function run($lang = 'ru')
    {
        switch ($lang){
            case 'pt':
                DB::table('project_statuses')->insert(array(
                    array('id' => '1', 'name' => 'Estado inicial', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
            case 'ru':
                DB::table('project_statuses')->insert(array(
                    array('id' => '1', 'name' => 'Начальный статус', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
            case 'en':
                DB::table('project_statuses')->insert(array(
                    array('id' => '1', 'name' => 'Initial status', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
        }
    }
}