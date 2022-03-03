<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/token', 'App\Http\Controllers\Auth\Auth0Controller@token');
Route::get('/auth0_users', 'App\Http\Controllers\ApiController@auth0_users');

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'App\Http\Controllers'], function ($api) {

            $api->group(['prefix' => 'contact3cx'], function ($api) {
                $api->get('/', 'Ip3CXController@client_by_number');
                $api->post('/create', 'Ip3CXController@create_contact');
            });

            $api->group(['prefix' => 'calls3cx'], function ($api) {
                $api->post('/create', 'Ip3CXController@create_call');
            });
            $api->get('/3cx/config', 'Ip3CXController@download_config');

            $api->get('projects/audit/{id}', 'AuditController@project')->where('id', '[0-9]+');

            //        $api->get('ping', 'Api\PingController@index');
            $api->post('/', 'ApiController@api'); // protected with site token
            //signed
            $api->get('/invoices/pdf/{id}', 'InvoiceController@pdf')->where('id', '[0-9]+');

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'me'], function ($api) {
                    $api->get('/', 'Api\v1\ProfileController@index');
                    $api->put('/', 'Api\v1\ProfileController@update');
                    $api->patch('/', 'Api\v1\ProfileController@update');
                    $api->post('/device', 'Api\v1\ProfileController@add_device');
                    $api->delete('/device', 'Api\v1\ProfileController@remove_device');

                    $api->get('/schedule', 'Api\v1\ProfileController@schedule');
                    $api->post('/schedule', 'Api\v1\ProfileController@save_schedule');
                    //                $api->put('/password', 'Api\ProfileController@updatePassword');
                });

                $api->group(['prefix' => 'groups'], function ($api) {
                    $api->get('/', 'Api\v1\GroupsController@index');
                });

                $api->group(['prefix' => 'sites'], function ($api) {
                    $api->get('/', 'Api\v1\SitesController@index');
                });

                $api->group(['prefix' => 'branches'], function ($api) {
                    $api->get('/', 'BranchController@index');
                });

                # NOT RESTFULL          
                $api->group(['prefix' => 'telegram'], function ($api) {
                    $api->post('/send_code', 'TelegramController@send_code');
                    $api->post('/enter_code', 'TelegramController@enter_code');
                });

                $api->get('/version', 'AuthController@version');
                $api->post('/start_tour',  'HomeController@start_tour');
                $api->post('/end_tour',  'HomeController@end_tour');

                $api->get('/notifications', 'HomeController@all_notifications');
                $api->get('/notifications/last', 'HomeController@last_notifications');
                $api->post('/notifications/read', 'HomeController@mark_as_read');
                $api->post('/notifications/read_all', 'HomeController@mark_all_as_read');
                $api->post('/photo_upload', 'HomeController@photo_upload');
                $api->post('/file_upload', 'HomeController@file_upload');
                $api->post('/action_sms', 'HomeController@action_sms');
                $api->post('/check', 'HomeController@check_real_time');
                //
                $api->get('/me/dashboard', 'HomeController@my_dashboard');
                $api->post('/me/dashboard/short_entity', '\Rkesa\Dashboard\Http\Controllers\DashboardController@short_entity');
                $api->post('/me/dashboard/full_entity', '\Rkesa\Dashboard\Http\Controllers\DashboardController@full_entity');
                $api->post('/me/dashboard/widget', '\Rkesa\Dashboard\Http\Controllers\DashboardController@widget');
                $api->post('/me/dashboard/widget_more', '\Rkesa\Dashboard\Http\Controllers\DashboardController@widget_more');

                $api->group(['prefix' => 'global_settings'], function ($api) {
                    $api->get('/', 'Api\v1\GlobalSettingsController@index');
                });

                // Google Drive
                $api->post('/settings/integrations/google_drive/upload', 'GoogleDriveController@upload');
                $api->post('/settings/integrations/google_drive/upload_with_job', 'GoogleDriveController@upload_with_job');
                $api->get('/settings/integrations/google_drive/job_status/{id}', 'GoogleDriveController@job_status')->where('id', '[0-9]+');

                $api->group(['prefix' => 'chats'], function ($api) {
                    $api->post('/', 'ChatController@store');
                    $api->get('/', 'ChatController@index');
                    //                    $api->get('/{id}', 'ChatController@show')->where('id', '[0-9]+');
                    //                    $api->put('/{id}', 'ChatController@update')->where('id', '[0-9]+');
                    //                    $api->patch('/{id}', 'ChatController@update')->where('id', '[0-9]+');
                    //                    $api->delete('/{id}', 'ChatController@destroy')->where('id', '[0-9]+');

                    // messages
                    $api->post('/{id}/messages', 'ChatMessageController@store')->where('id', '[0-9]+');
                    $api->get('/{id}/messages', 'ChatMessageController@index')->where('id', '[0-9]+');
                    //                    $api->get('/{id}/messages/{message_id}', 'ChatMessageController@show')->where('id', '[0-9]+');
                    //                    $api->put('/{id}/messages/{message_id}', 'ChatMessageController@update')->where('id', '[0-9]+');
                    //                    $api->patch('/{id}/messages/{message_id}', 'ChatMessageController@update')->where('id', '[0-9]+');
                    //                    $api->delete('/{id}/messages/{message_id}', 'ChatMessageController@destroy')->where('id', '[0-9]+');
                    $api->get('/users', 'ChatUsersController@index');
                    $api->post('/file_upload', 'ChatMessageController@file_upload');
                });

                $api->post('/chat_messages/{id}/make_as_read', 'ChatMessageController@make_as_read')->where('id', '[0-9]+');

                $api->group(['prefix' => '/settings/integrations/mailchimp'], function ($api) {
                    $api->get('/audiences', 'MailChimpController@audiences');
                    $api->get('/campaigns', 'MailChimpController@campaigns');
                    $api->post('/audiences/create', 'MailChimpController@create_new_audience');
                    $api->post('/audiences/add', 'MailChimpController@add_to_audience');
                });

                $api->group(['middleware' => 'is_admin'], function ($api) {
                    $api->post('/import_settings', 'ImportController@import_settings');
                    $api->post('/import_data', 'ImportController@import_data');
                    $api->post('/clear_popups', 'HomeController@clear_popups');

                    $api->group(['prefix' => 'groups'], function ($api) {
                        $api->put('/', 'Api\v1\GroupsController@update');
                        $api->patch('/', 'Api\v1\GroupsController@update');
                    });

                    $api->post('/hr/{user_id}/permissions', 'UserController@permissions_save')->where('user_id', '[0-9]+');
                    $api->post('/hr/{user_id}/dashboard_widgets', 'UserController@save_widgets_order')->where('user_id', '[0-9]+');

                    $api->post('/settings/integrations', 'SettingsController@integration_save');
                    $api->post('/settings/sites', 'SettingsController@site_save');
                    $api->post('/settings', 'SettingsController@save_settings');

                    $api->group(['prefix' => 'global_settings'], function ($api) {
                        $api->post('/', 'Api\v1\GlobalSettingsController@global_save');
                    });

                    // Google Drive
                    $api->get('/settings/integrations/google_drive', 'GoogleDriveController@auth');
                    // Google Calendar
                    $api->get('/settings/integrations/google_calendar', 'GoogleCalendarController@auth');
                    // Facebook
                    $api->get('/settings/integrations/facebook', 'FacebookController@auth');

                    // $api->post('/subscriptions/start_trial', 'SubscriptionController@start_trial');
                    $api->post('/subscriptions/from_balance', 'SubscriptionController@pay_from_balance');
                    $api->post('/subscriptions/with_invoice', 'SubscriptionController@pay_with_invoice');

                    $api->get('/braintree/token', 'BrainTreeController@_token');
                    $api->post('/braintree/nonce', 'BrainTreeController@receive_nonce');

                    $api->get('/payments', 'PaymentController@index');
                    $api->get('/payments/paging', 'PaymentController@paging');
                    $api->get('/payments/download_invoice', 'PaymentController@download_invoice');

                    $api->post('/company_information', 'CompanyInformationController@save');
                });

                $api->get('/company_information', 'CompanyInformationController@index');
                $api->get('/modules', 'ModuleController@index');
                $api->get('/calls', 'CallsController@index');
                $api->get('/email_templates', 'EmailTemplateController@index');


                $api->group(['prefix' => 'invoices'], function ($api) {
                    $api->post('/export', 'InvoiceController@export');

                    $api->group(['middleware' => 'user_can', 'collection' => 'invoices', 'action' => 'read'], function ($api) {
                        $api->get('/', 'InvoiceController@index');
                        $api->get('/{id}', 'InvoiceController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'invoices', 'action' => 'create'], function ($api) {
                        $api->post('/', 'InvoiceController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'invoices', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'InvoiceController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'InvoiceController@update')->where('id', '[0-9]+');
                        $api->post('/pay/{id}', 'InvoiceController@pay')->where('id', '[0-9]+');
                        $api->get('/saft', 'InvoiceController@get_saft');
                    });

                    $api->group(['middleware' => 'user_can', 'collection' => 'invoices', 'action' => 'delete'], function ($api) {
                        // $api->delete('/{id}', 'InvoiceController@destroy')->where('id', '[0-9]+');
                        $api->post('/cancel/{id}', 'InvoiceController@cancel')->where('id', '[0-9]+');
                    });
                    $api->get('/get_link/{id}', 'InvoiceController@get_pdf_link')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'payment_conditions'], function ($api) {
                    $api->get('/', 'PaymentConditionController@index');
                });

                $api->group(['prefix' => 'movement_types'], function ($api) {
                    $api->get('/', 'MovementTypeController@index');
                });

                $api->group(['prefix' => 'vat_types'], function ($api) {
                    $api->get('/', 'VatTypeController@index');
                });

                $api->group(['prefix' => 'vat_exemption_reasons'], function ($api) {
                    $api->get('/', 'VatExemptionReasonController@index');
                });

                $api->group(['prefix' => 'invoice_document_types'], function ($api) {
                    $api->get('/', 'InvoiceDocumentTypeController@index');
                });

                $api->group(['prefix' => 'invoice_series'], function ($api) {
                    $api->get('/', 'InvoiceSeriesController@index');
                    $api->post('/', 'InvoiceSeriesController@store');
                });

                $api->group(['prefix' => 'products'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'products', 'action' => 'create'], function ($api) {
                        $api->post('/', 'ProductController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'products', 'action' => 'read'], function ($api) {
                        $api->get('/', 'ProductController@index');
                        $api->get('/{id}', 'ProductController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'products', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'ProductController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ProductController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'products', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'ProductController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'contractor_service_pay_stages'], function ($api) {
                    $api->put('/{id}', 'ContractorServicePayStageController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ContractorServicePayStageController@update')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'google_ads'], function ($api) {
                    $api->post('/get_refresh_token', 'GoogleAdsController@get_refresh_token');
                    $api->get('/campaigns', 'GoogleAdsController@campaigns');
                });

                $api->group(['prefix' => 'connection'], function ($api) {
                    $api->get('/', 'ConnectionController@index');
                    $api->post('/', 'ConnectionController@store');
                    $api->patch('/', 'ConnectionController@update');
                    $api->post('/confirm', 'ConnectionController@confirm');
                });

                $api->post('/invoice_settings', 'InvoiceController@update_settings');
                $api->post('/vat_type_settings', 'VatTypeController@update_settings');

                $api->post('/estimates/import_data', 'ImportController@import_estimate');
            });
        });
    });
});
