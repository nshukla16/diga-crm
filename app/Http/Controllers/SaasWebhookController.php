<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\ModuleController;

class SaasWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        if (env('APP_ENV') == 'local' || $request->ip() == '81.84.253.89') {
            $type = $request->input('type', '');
            switch ($type) {
                case 'google_calendar':
                    $user_id = $request->input('user_id', null);
                    GoogleCalendarController::synchronize($user_id);
                    break;
                case 'google_drive':
                    GoogleDriveController::done();
                    break;
                case 'facebook':
                    FacebookController::get_pages_list();
                    break;
                case 'facebook_message':
                    FacebookController::fb_incoming_message($request['page_id'], $request['sender_id'], $request['text']);
                    break;
                case 'techsupport':
                    ChatMessageController::message_from_tech_support($request['user_id'], $request['message']);
                    break;
                case 'pricegroups':
                    ModuleController::receive_from_saas($request['pm_data'], $request['pg_data']);
                    break;
                case 'order_approved':
                    SubscriptionController::approve_payment_from_saas($request['payment']);
                    break;
                case 'modules_updated':
                    ModuleController::update_tariffs_and_modules($request['modules'], $request['number_of_users']);
                    break;
                case 'new_connection':
                    ConnectionController::receive($request);
                    break;
                case 'confirm_connection':
                    ConnectionController::receive_confirm($request);
                    break;
                case 'create_service':
                    ExternalServiceController::store($request);
                    break;
                case 'change_service_status_from_contractor':
                    ExternalServiceController::change_service_status_from_contractor($request);
                    break;
                case 'change_service_status_from_general_contractor':
                    ExternalServiceController::change_service_status_from_general_contractor($request);
                    break;
                case 'upload_invoice_status_from_contractor':
                    ExternalPayStageController::upload_invoice_status_from_contractor($request);
                    break;
                case 'paid_from_general_contractor':
                    ExternalPayStageController::paid_from_general_contractor($request);
                    break;
                case '':
            }
        } else {
            Log::info('WARNING!!! - SAAS WEBHOOK FROM UNRECOGNIZED SERVER: ' . $request->ip());
            throw new Exception('WARNING!!! - SAAS WEBHOOK FROM UNRECOGNIZED SERVER: ' . $request->ip());
        }
    }
}
