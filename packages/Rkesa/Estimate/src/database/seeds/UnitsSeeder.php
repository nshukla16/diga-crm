<?php

namespace Rkesa\Estimate\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder {

    public function run($lang = 'ru')
    {
        switch ($lang){
            case 'pt':
                DB::table('estimate_units')->insert(array(
                    array('id' => '1', 'measure' => 'm2'),
                    array('id' => '2', 'measure' => 'm3'),
                    array('id' => '3', 'measure' => 'hr'),
                    array('id' => '4', 'measure' => 'ml'),
                    array('id' => '5', 'measure' => 'm2/ml'),
                    array('id' => '6', 'measure' => 'kg'),
                    array('id' => '7', 'measure' => 'l'),
                    array('id' => '8', 'measure' => 'un'),
                    array('id' => '9', 'measure' => 'vg'),
                    array('id' => '10', 'measure' => ''),
                    array('id' => '11', 'measure' => 'dia'),
                ));
                break;
            case 'ru':
                DB::table('estimate_units')->insert(array(
                    array('id' => '1', 'measure' => 'м2'),
                    array('id' => '2', 'measure' => 'м3'),
                    array('id' => '3', 'measure' => 'часы'),
                    array('id' => '4', 'measure' => 'мл'),
                    array('id' => '5', 'measure' => 'м2/мл'),
                    array('id' => '6', 'measure' => 'кг'),
                    array('id' => '7', 'measure' => 'л'),
                    array('id' => '8', 'measure' => 'шт'),
                    //array('id' => '9', 'measure' => 'vg'), // what is it?
                    array('id' => '10', 'measure' => ''),
                    array('id' => '11', 'measure' => 'дни'),
                ));
                break;
            case 'en':
                DB::table('estimate_units')->insert(array(
                    array('id' => '1', 'measure' => 'm2'),
                    array('id' => '2', 'measure' => 'm3'),
                    array('id' => '3', 'measure' => 'hr'),
                    array('id' => '4', 'measure' => 'ml'),
                    array('id' => '5', 'measure' => 'm2/ml'),
                    array('id' => '6', 'measure' => 'kg'),
                    array('id' => '7', 'measure' => 'l'),
                    array('id' => '8', 'measure' => 'un'),
                    array('id' => '9', 'measure' => 'vg'),
                    array('id' => '10', 'measure' => ''),
                    array('id' => '11', 'measure' => 'day'),
                ));
                break;
        }
    }
}