<?php

namespace Rkesa\Client\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferrersSeeder extends Seeder {

    public function run($lang = 'ru')
    {
        switch ($lang){
            case 'pt':
                DB::table('client_referrers')->insert(array(
                    array('id' => '1', 'title' => 'Recomendação'),
                    array('id' => '2', 'title' => 'Boca-a-boca'),
                    array('id' => '3', 'title' => 'Facebook'),
                    array('id' => '4', 'title' => 'Desconhecida'),
                    array('id' => '5', 'title' => 'Email'),
                    array('id' => '6', 'title' => 'Outro'),
                ));
                break;
            case 'ru':
                DB::table('client_referrers')->insert(array(
                    array('id' => '1', 'title' => 'Рекомендация'),
                    array('id' => '2', 'title' => 'По слухам'),
                    array('id' => '3', 'title' => 'Facebook'),
                    array('id' => '4', 'title' => 'Неизвестно'),
                    array('id' => '5', 'title' => 'Email'),
                    array('id' => '6', 'title' => 'Другой'),
                ));
                break;
            case 'en':
                DB::table('client_referrers')->insert(array(
                    array('id' => '1', 'title' => 'Recommendation'),
                    array('id' => '2', 'title' => 'Mouth to mouth'),
                    array('id' => '3', 'title' => 'Facebook'),
                    array('id' => '4', 'title' => 'Unknown'),
                    array('id' => '5', 'title' => 'Email'),
                    array('id' => '6', 'title' => 'Other'),
                ));
                break;
        }
    }
}