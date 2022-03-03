<?php

namespace Rkesa\Calendar\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTypesSeeder extends Seeder {

    public function run($lang = 'ru')
    {
        switch ($lang){
            case 'pt':
                DB::table('event_types')->insert(array(
                    array('id' => '1', 'title' => 'Chamada', 'color' => '#0070BF', 'icon' => 'fa-phone', 'order' => '1'),
                    array('id' => '2', 'title' => 'Visita marcada', 'color' => '#92CF50', 'icon' => 'fa-car', 'order' => '2'),
                    array('id' => '3', 'title' => 'Marcar visita', 'color' => '#FE0000', 'icon' => 'fa-pencil', 'order' => '3'),
                    array('id' => '4', 'title' => 'Orçamentar', 'color' => '#7030A0', 'icon' => 'fa-file-text-o', 'order' => '4'),
                    array('id' => '5', 'title' => 'Reunião ou adjudicação', 'color' => '#000000', 'icon' => 'fa-paper-plane', 'order' => '5'),
                    array('id' => '6', 'title' => 'Primeiro contacto', 'color' => '#FE0000', 'icon' => 'fa-user', 'order' => '6'),
                    array('id' => '7', 'title' => 'Outro', 'color' => '#EC7D31', 'icon' => 'fa-long-arrow-right', 'order' => '7'),
                ));
                break;
            case 'ru':
                DB::table('event_types')->insert(array(
                    array('id' => '1', 'title' => 'Позвонить', 'color' => '#0070BF', 'icon' => 'fa-phone', 'order' => '1'),
                    array('id' => '2', 'title' => 'Выполнить визит', 'color' => '#92CF50', 'icon' => 'fa-car', 'order' => '2'),
                    array('id' => '3', 'title' => 'Назначить визит', 'color' => '#FE0000', 'icon' => 'fa-pencil', 'order' => '3'),
                    array('id' => '4', 'title' => 'Составить смету', 'color' => '#7030A0', 'icon' => 'fa-file-text-o', 'order' => '4'),
                    array('id' => '5', 'title' => 'Встреча или договор', 'color' => '#000000', 'icon' => 'fa-paper-plane', 'order' => '5'),
                    array('id' => '6', 'title' => 'Первичный контакт', 'color' => '#FE0000', 'icon' => 'fa-user', 'order' => '6'),
                    array('id' => '7', 'title' => 'Другое', 'color' => '#EC7D31', 'icon' => 'fa-long-arrow-right', 'order' => '7'),
                ));
                break;
            case 'en':
                DB::table('event_types')->insert(array(
                    array('id' => '1', 'title' => 'Make a call', 'color' => '#0070BF', 'icon' => 'fa-phone', 'order' => '1'),
                    array('id' => '2', 'title' => 'Make a visit', 'color' => '#92CF50', 'icon' => 'fa-car', 'order' => '2'),
                    array('id' => '3', 'title' => 'Schedule a visit', 'color' => '#FE0000', 'icon' => 'fa-pencil', 'order' => '3'),
                    array('id' => '4', 'title' => 'Make a budget', 'color' => '#7030A0', 'icon' => 'fa-file-text-o', 'order' => '4'),
                    array('id' => '5', 'title' => 'Meeting or agreement', 'color' => '#000000', 'icon' => 'fa-paper-plane', 'order' => '5'),
                    array('id' => '6', 'title' => 'Primary contact', 'color' => '#FE0000', 'icon' => 'fa-user', 'order' => '6'),
                    array('id' => '7', 'title' => 'Other', 'color' => '#EC7D31', 'icon' => 'fa-long-arrow-right', 'order' => '7'),
                ));
                break;
        }
    }
}