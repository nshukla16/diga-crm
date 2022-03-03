<?php

namespace App\Http\Controllers\Zadarma\Notifications;

use stdClass;
use Exception;
use App\Call;
use App\Events\CallContact;
use Rkesa\Client\Models\ClientReferrer;

class NOTIFY_INTERNAL extends BASE_NOTIFICATION implements INTERFACE_NOTIFICATION {

    public function handle(array $data, $settings) {

        $user_id = $this->user_id($data, $settings);
        $contact = $this->getContactByPhoneNumber($data['caller_id']);
        $referrer_id = ClientReferrer::firstOrCreate(['title' => trans('zadarma.Call')])->id;

        $out = new stdClass;
        $out->user_id = $user_id;

        if($contact === null) {
            $out->case = 'new_contact';
            $out->caller_id = str_replace('+', '', $data['caller_id']);
            $out->referrer_id = $referrer_id;
        } else {
            $out->case = 'existing_contact';
            $out->contact_id = $contact->id;
            $out->contact_name = sprintf('%s %s', $contact->name, $contact->surname);
        }
        
        event(new CallContact($out));
        
    }

}
