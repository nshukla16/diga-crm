<?php

namespace App\Http\Controllers\Zadarma\Notifications;

use App\Call;
use App\User;
use Exception;
use Carbon\Carbon;
use Rkesa\Calendar\Models\Event;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewMissedCall;
use App\Http\Controllers\Zadarma\Number;
use App\Http\Controllers\Zadarma\History;
use App\Http\Controllers\Zadarma\Internal;
use App\Http\Controllers\Zadarma\Settings;
use Illuminate\Notifications\DatabaseNotification;

class NOTIFY_END extends BASE_NOTIFICATION implements INTERFACE_NOTIFICATION {

    public function handle(array $data, $settings) {
        $numbers = new Number;
        $allnumbers = $numbers->getAllNumbers();

        $isInternal = array_search($data['caller_id'], $allnumbers->internal) === false;
        $isIncome = array_search($data['called_did'], $allnumbers->direct) !== false;
        $isMissed = array_search($data['disposition'], array('answered')) === false;

        $user_id = $this->user_id($data, $settings);
        $contact_side = $isIncome ? $data['caller_id'] : $data['called_did'];
        $contact = $this->getContactByPhoneNumber($contact_side, true);

        $call = new Call;
        $call->fill($data);
        $call->user_id = $user_id;
        $call->save();

       
        if($contact != null && $user_id != 0) {
            $type_id = $isIncome ? 21 : 22;

            $history = new History;
            $history->write_history($user_id, $contact->id, $type_id, $call->id);
        }

        if($isMissed) {
            if($isIncome) {
                if($contact != null) {
                    if($settings->zadarma_new_task_if_no_answer == 1) {
                        $responsible_user = User::where('id', '=', $settings->zadarma_missed_call_responsible_id)->first();

                        if($responsible_user === null)
                            return;

                        $event = new Event;
                        $event->user_id = $responsible_user->id;
                        $event->event_type_id = $settings->zadarma_task_type_id;
                        $event->creator_user_id = $responsible_user->id;
                        $event->client_contact_id = $contact->id;
                        $event->start = Carbon::now();
                        $event->description = sprintf( trans('zadarma.CallbackForUnanswered'),
                            $data['caller_id'], Carbon::now() );
                        $event->done = 0;
                        $event->save();

                        $response_event = array(
                            'id' => $event->id,
                            'event_type_id' => $settings->zadarma_task_type_id,
                            'client_contact' => array(
                                'name'  => $contact->name,
                                'surname'=> $contact->surname,
                                'client' => array(
                                    'name' => $contact->client ? $contact->client->name : ''
                                ),
                                'client_id' => $contact->client ? $contact->client->id : ''
                            ),
                            'client_contact_id' => $contact->id
                        );

                        $response_user = array(
                            'id'  => $responsible_user->id,
                            'name' => $responsible_user->name
                        );

                        $responsible_user->notify(new TaskAssigned($response_event, $response_user ));
                    }
                }
                else{                    
                    $users_to_notify = User::where('missed_calls_notifications', '=', true)->get();
                    foreach($users_to_notify as $utn){
                        $utn->notify(new NewMissedCall($call));
                    }
                }
            }
        }
    }
}
