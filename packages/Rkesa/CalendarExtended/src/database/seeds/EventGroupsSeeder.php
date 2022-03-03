<?php

namespace Rkesa\CalendarExtended\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventGroupsSeeder extends Seeder {

    public function run($lang = 'ru')
    {
        switch ($lang){
            case 'pt':
                DB::table('event_groups')->insert(array(
                    array('id' => '1', 'name' => 'Sem grupo', 'color' => '#0070BF'),
                    array('id' => '2', 'name' => 'Grupo 1', 'color' => '#0070BF'),
                    array('id' => '3', 'name' => 'Grupo 2', 'color' => '#0070BF'),
                ));
                break;
            case 'ru':
                DB::table('event_groups')->insert(array(
                    array('id' => '1', 'name' => 'Без группы', 'color' => '#0070BF'),
                    array('id' => '2', 'name' => 'Группа 1', 'color' => '#0070BF'),
                    array('id' => '3', 'name' => 'Группа 2', 'color' => '#0070BF'),
                ));
                break;
            case 'en':
                DB::table('event_groups')->insert(array(
                    array('id' => '1', 'name' => 'Without group', 'color' => '#0070BF'),
                    array('id' => '2', 'name' => 'Group 1', 'color' => '#0070BF'),
                    array('id' => '3', 'name' => 'Group 2', 'color' => '#0070BF'),
                ));
                break;
        }
    }
}