<?php

namespace App\Http\Controllers\Zadarma;

use Log;
use Exception;

class Notify {
    
    private $notifications = array(
        'NOTIFY_START',
        'NOTIFY_INTERNAL',
        'NOTIFY_ANSWER',
        'NOTIFY_END',
        'NOTIFY_OUT_START',
        'NOTIFY_OUT_END',
        'NOTIFY_RECORD'
    );

    public function __construct(array $data, $settings) {
        if(!array_key_exists('event', $data))
            throw new Exception("Event type not found");

        if(!$this->isValidNotification($data['event']))
            throw new Exception("Invalid event");

        if(!array_key_exists('pbx_call_id', $data))
            throw new Exception("Pbx_call_id missed.");

        $notificaton = __NAMESPACE__ . '\\Notifications\\' . $data['event'];
        $the_notification = new $notificaton;
        $the_notification->handle($data, $settings);
        Log::info('call', $data);
    }

    private function isValidNotification(string $notificaton) {
        return array_search($notificaton, $this->notifications) !== false;
    }
}
