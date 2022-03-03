<?php

namespace App\Http\Controllers\Zadarma\Notifications;

use App\Call;

interface INTERFACE_NOTIFICATION {

    public function handle(array $data, $settings);

}
