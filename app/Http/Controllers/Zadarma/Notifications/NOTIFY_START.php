<?php

namespace App\Http\Controllers\Zadarma\Notifications;

use App\GlobalSettings;
use Exception;
use App\Call;
use App\User;
use App\Http\Controllers\Zadarma\Settings as Zsettings;

class NOTIFY_START extends BASE_NOTIFICATION implements INTERFACE_NOTIFICATION {

    private $user_fields = array('id','zadarma_internal_phonecode');

    public function handle(array $data, $settings) {

        $params = array();

        $contact = $this->getContactByPhoneNumber($data['caller_id']);
        if($contact) {
            $params['caller_name'] = sprintf('%s %s', $contact->name, $contact->surname);

            if($contact->responsible_user_id) {
                $user = User::select($this->user_fields)->find($contact->responsible_user_id);
                $gs = GlobalSettings::first();

                if($user && $gs->zadarma_redirect_to_responsible) {
                    $params['redirect'] = $user->zadarma_internal_phonecode;
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($params));

    }

}
