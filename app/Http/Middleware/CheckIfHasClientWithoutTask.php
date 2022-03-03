<?php

namespace App\Http\Middleware;

use App\GlobalSettings;
use Closure;
use Illuminate\Support\Facades\DB;
use Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Rkesa\Calendar\Models\Event;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientHistory;

class CheckIfHasClientWithoutTask
{

    public function handle($request, Closure $next, $guard = null)
    {
        $gs = GlobalSettings::first();
        if ($gs && $gs->check_clients_no_tasks) {
            $user = $request->user();

//            if ($user) {
//                $clients = Client::where('ignore_doesnt_have_tasks', false)
//                    ->whereRaw('not exists (select * from `events` where `clients`.`id` = `events`.`client_id` and `done` = 0)')
//                    ->get(); // get clients that dont have active tasks
//
//                $redirect_to_client_id = null;
//                foreach ($clients as $client) {
//                    if ($client->redirect_to_this_client()) {
//                        $redirect_to_client_id = $client->id;
//                        break;
//                    }
//                }
//
//                $redirect_url = env('APP_URL') . '/companies/' . $redirect_to_client_id;
//                $current_url = url()->current();
//                if ($redirect_to_client_id != null &&
//                    $current_url != $redirect_url &&
//                    strpos($current_url, 'css') == false &&
//                    strpos($current_url, 'js') == false &&
//                    $current_url != env('APP_URL') . '/clients/' . $redirect_to_client_id . '/ignore_no_tasks' &&
//                    $current_url != env('APP_URL') . '/clients/' . $redirect_to_client_id . '/events' &&
//                    $current_url != env('APP_URL') . '/broadcasting/auth' &&
//                    $current_url != env('APP_URL') . '/clients/get_new_requests' &&
//                    $current_url != env('APP_URL') . '/clients/get_new_fb_messages' &&
//                    $current_url != env('APP_URL') . '/set_language') {
//                    if ($request->isMethod('get')) {
//                        $request->session()->put('no_tasks', true);
//                        return redirect($redirect_url);
//                    } else {
//                        Log::info($current_url);
//                        throw new Exception('Check client without task');
//                    }
//                }
//            }
        }

        return $next($request);
    }
}
