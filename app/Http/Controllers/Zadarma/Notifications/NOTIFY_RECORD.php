<?php

namespace App\Http\Controllers\Zadarma\Notifications;

use Exception;
use App\Http\Controllers\Zadarma\Api;
use App\Http\Controllers\Zadarma\History;

class NOTIFY_RECORD extends BASE_NOTIFICATION implements INTERFACE_NOTIFICATION{

    public function handle(array $data, $settings) {

        $call = $this->getCall($data['pbx_call_id']);

        if($call != null) {
            if(!$call->is_recorded)
                return;

            $api = new Api;
            $call_record = $api->getCallRecordLink($data['call_id_with_rec']);

            if($call_record !== false) {
                History::updateCallRecord($call->id, $call_record->link, $call_record->lifetime_till);
            }
        }
    }
}
