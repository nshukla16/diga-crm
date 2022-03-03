<?php

namespace App\Http\Controllers\Zadarma\Notifications;

use Exception;
use App\Call;
use App\Http\Controllers\Zadarma\Number;
use App\Http\Controllers\Zadarma\History;

class NOTIFY_OUT_END extends BASE_NOTIFICATION implements INTERFACE_NOTIFICATION {

    public function handle(array $data, $settings) {
        $number = new Number();

        $allNumbers = $number->getAllNumbers();

        $isCallbackCall = array_search((int) $data['caller_id'], $allNumbers->direct);

        if($isCallbackCall !== false) {
            $contact = $this->getContactByPhoneNumber($data['destination'], true);
            $user_id = $this->user_id($data, $settings);

            $call = new Call;
            $call->fill($data);
            $call->user_id = $user_id;
            $call->called_did = $data['destination'];
            $call->save();
            
            if($contact != null) {
                $type_id = 22;

                $history = new History;
                $history->write_history($user_id, $contact->id, $type_id, $call->id);
            }
        }
    }
}
