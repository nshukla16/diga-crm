<?php

namespace App\Http\Controllers\Zadarma;

use App\GlobalSettings;

class Settings {

    public static function getSettings(){
        return GlobalSettings::select(array(
            'zadarma_enabled',
            'zadarma_key',
            'zadarma_secret',
            'zadarma_redirect_to_responsible',
            'zadarma_new_task_if_no_answer',
            'zadarma_task_type_id',
            'zadarma_missed_call_responsible_id'
        ))->first();
    }

    public function update() {}
    public function reset() {}

}
