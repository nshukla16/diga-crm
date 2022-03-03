<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {
        $api->group(['namespace' => 'Rkesa\Client\Http\Controllers'], function ($api) {

            $api->get('/companies/pdf/{id}', 'ClientController@show_company')->where('id', '[0-9]+'); // signed url
            $api->get('/clients/autocomplete', 'ClientController@webmail_autocomplete'); // used by afterlogic webmail
            $api->post('/clients/outcoming_email', 'ClientController@outcoming_email'); // used by afterlogic webmail
            $api->post('/clients/incoming_email', 'ClientController@incoming_email'); // used by afterlogic webmail

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'client_referrers'], function ($api) {
                    $api->get('/', 'ClientReferrerController@index');
                });

                $api->group(['prefix' => 'companies'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'create'], function ($api) {
                        $api->post('/', 'ClientController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'read'], function ($api) {
                        $api->get('/', 'ClientController@index');
                        $api->get('/{id}', 'ClientController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'ClientController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ClientController@update')->where('id', '[0-9]+');
                        $api->post('/{id}/save_note', 'ClientController@save_note')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'ClientController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->post('/zadarma/callback', 'ContactController@zadarma_request_callback');

                $api->group(['prefix' => 'clients'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'create'], function ($api) {
                        $api->post('/', 'ContactController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'read'], function ($api) {
                        $api->get('/', 'ContactController@index');
                        $api->get('/{id}', 'ContactController@show')->where('id', '[0-9]+');
                        $api->get('/history/{id}', 'ClientHistoryController@index')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'ContactController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ContactController@update')->where('id', '[0-9]+');
                        $api->post('/{id}/save_note', 'ContactController@save_note')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'ContactController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'contacts'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'create'], function ($api) {
                        $api->post('/', 'ContactController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'read'], function ($api) {
                        $api->get('/', 'ContactController@index');
                        $api->get('/{id}', 'ContactController@show')->where('id', '[0-9]+');
                        $api->get('/history/{id}', 'ContactHistoryController@index')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'ContactController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ContactController@update')->where('id', '[0-9]+');
                        $api->post('/{id}/save_note', 'ContactController@save_note')->where('id', '[0-9]+');
                        $api->post('/{id}/set_main', 'ContactController@setMainContact')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'clients', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'ContactController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['middleware' => 'is_admin'], function ($api) {
                    $api->post('/settings/clients', 'ClientSettingsController@save');
                });

                $api->group(['prefix' => 'calculations'], function ($api) {
                    $api->post('/', 'ClientCalculationController@store');
                    $api->put('/{id}', 'ClientCalculationController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ClientCalculationController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'ClientCalculationController@destroy')->where('id', '[0-9]+');
                });

                # NOT RESTFULL
                $api->get('/clients/{id}/ignore_no_tasks', 'ClientController@ignore_no_tasks')->where('id', '[0-9]+');
                $api->post('/contacts/{id}/fb_message', 'ContactController@fb_message')->where('id', '[0-9]+');
                $api->post('/clients/get_aru_feature', 'AruController@ARUzones');
                $api->get('/clients/get_new_requests', 'ClientController@get_new_requests');
                $api->get('/clients/get_new_fb_messages', 'ClientController@get_new_fb_messages');
                $api->post('/clients/{id}/history', 'ClientController@history_message')->where('id', '[0-9]+');
                $api->get('/companies/get_link/{id}', 'ClientController@get_company_pdf_link')->where('id', '[0-9]+');

                $api->post('/clients/invoice_email', 'InvoiceEmailController@generate');
                $api->get('/history/{id}/message', 'ContactHistoryController@message')->where('id', '[0-9]+');
            });
        });
    });
});
