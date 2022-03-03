<?php

namespace Rkesa\Service\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypesSeeder extends Seeder {

	public function run($lang = 'ru') {
        switch ($lang){
            case 'pt':
                DB::table('service_types')->insert(array(
                    array('id' => '1', 'name' => 'Interior'),
                    array('id' => '2', 'name' => 'Exterior'),
                    array('id' => '3', 'name' => 'Interior/Exterior'),
                    array('id' => '4', 'name' => 'Outros'),
                ));
                break;
            case 'ru':
                DB::table('service_types')->insert(array(
                    array('id' => '1', 'name' => 'Внутренние работы'),
                    array('id' => '2', 'name' => 'Внешние работы'),
                    array('id' => '3', 'name' => 'Внутренние/внешние'),
                    array('id' => '4', 'name' => 'Другое'),
                ));
                break;
            case 'en':
                DB::table('service_types')->insert(array(
                    array('id' => '1', 'name' => 'Internal works'),
                    array('id' => '2', 'name' => 'External works'),
                    array('id' => '3', 'name' => 'Internal/External'),
                    array('id' => '4', 'name' => 'Other'),
                ));
                break;
        }
	}
}