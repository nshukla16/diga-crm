<?php

namespace Rkesa\Service\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicePrioritiesSeeder extends Seeder {

	public function run($lang = 'ru') {
        switch ($lang){
            case 'pt':
                DB::table('service_priorities')->insert(array(
                    array('id' => '1', 'name' => 'Normal'),
                    array('id' => '2', 'name' => 'Urgente'),
                    array('id' => '3', 'name' => 'Muito urgente'),
                    array('id' => '4', 'name' => 'Sem termo'),
                ));
                break;
            case 'ru':
                DB::table('service_priorities')->insert(array(
                    array('id' => '1', 'name' => 'Обычный'),
                    array('id' => '2', 'name' => 'Срочный'),
                    array('id' => '3', 'name' => 'Очень срочный'),
                    array('id' => '4', 'name' => 'Без срока'),
                ));
                break;
            case 'en':
                DB::table('service_priorities')->insert(array(
                    array('id' => '1', 'name' => 'Normal'),
                    array('id' => '2', 'name' => 'Urgent'),
                    array('id' => '3', 'name' => 'Very urgent'),
                    array('id' => '4', 'name' => 'No time limit'),
                ));
                break;
        }
	}
}