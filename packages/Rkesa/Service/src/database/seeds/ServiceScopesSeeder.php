<?php

namespace Rkesa\Service\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceScopesSeeder extends Seeder {

    public function run($lang = 'ru') {
        DB::table('service_scopes')->insert(array(
            array('id' => '1', 'start_service_state_id' => 1, 'end_service_state_id' => 20, 'name' => trans('template.Main_scope', [], $lang)),
        ));
    }
}