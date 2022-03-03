<?php

namespace App\Http\Controllers\Zadarma\Notifications;

use App\Call;
use App\User;
//use Rkesa\Client\Models\Client;
//use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactPhone;
use App\GlobalSettings;

class BASE_NOTIFICATION {

    public function getCall(string $pbx_call_id) {
        return Call::where('pbx_call_id', '=', $pbx_call_id)->first();
    }

    public function getUserIdByInternalNumber(int $number) {
        $user = User::select(array('id'))->where('zadarma_internal_phonecode', '=', $number)->first();
        return ($user == null) ? null : $user->id;
    }

    public function getContactByPhoneNumber($phone_number, $withClient = false) {

        $with = array(
            'client_contact'
        );

        if($withClient)
            array_push($with, 'client_contact.client');

        $n = str_replace('+', '', $phone_number);

        $m = ClientContactPhone::with($with)->where('phone_number', 'LIKE', '%'.$n.'%')->first();

        if($m !== null)
            $m = $m->client_contact;

        return $m;
    }

    public function user_id(array $data, GlobalSettings $settings){
        if(array_key_exists('internal', $data)) {
            // getUserIdByInternalNumber can return null
            $user_id = $this->getUserIdByInternalNumber($data['internal']);
        } else {
            $user_id = null;
        }

        return $user_id;
    }

}
