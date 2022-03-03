<?php

namespace Rkesa\Project\Seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectTypesSeeder extends Seeder {

    public function run($lang = 'ru')
    {
        switch ($lang){
            case 'pt':
                DB::table('project_types')->insert(array(
                    array('id' => '1', 'name' => 'Fornecimento e instalação', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '2', 'name' => 'Fornecimento', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '3', 'name' => 'Instalação', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '4', 'name' => 'Serviços', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
            case 'ru':
                DB::table('project_types')->insert(array(
                    array('id' => '1', 'name' => 'Поставка и установка', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '2', 'name' => 'Поставка', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '3', 'name' => 'Установка', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '4', 'name' => 'Услуги', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
            case 'en':
                DB::table('project_types')->insert(array(
                    array('id' => '1', 'name' => 'Supply and Installation', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '2', 'name' => 'Supply', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '3', 'name' => 'Installation', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                    array('id' => '4', 'name' => 'Services', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()),
                ));
                break;
        }
    }
}