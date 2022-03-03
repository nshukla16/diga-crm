<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\Service\Http\Controllers'], function ($api) {

            //
            $api->post('/service_referrer/register', 'ServiceReferrerController@register');
            $api->post('/service_referrer/join_chat', 'ServiceReferrerController@join_chat');
            $api->post('/service_referrer/login', 'ServiceReferrerController@login');
            $api->get('/service_referrer/check_hash/{hash}', 'ServiceReferrerController@check_hash');
            $api->get('/service_referrer/users/{access_token}', 'ServiceReferrerController@users');

            $api->get('/service_referrer_chat/chats/{token}', 'ServiceReferrerChatController@chats');
            $api->get('/service_referrer_chat/{hash}/{id}/messages', 'ServiceReferrerChatController@messages');
            $api->post('/service_referrer_chat/{hash}/{id}/messages', 'ServiceReferrerChatController@post');
            $api->post('/service_referrer_chat/{hash}/upload', 'ServiceReferrerChatController@file_upload');


            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'service_states'], function ($api) {
                    $api->get('/', 'ServiceStatesController@index');
                    $api->get('/services', 'ServiceStatesController@services');
                });

                $api->group(['prefix' => 'service_scopes'], function ($api) {
                    $api->get('/', 'ServiceScopesController@index');
                });

                $api->group(['prefix' => 'service_types'], function ($api) {
                    $api->get('/', 'ServiceTypesController@index');
                });

                $api->group(['prefix' => 'service_priorities'], function ($api) {
                    $api->get('/', 'ServicePrioritiesController@index');
                });

                $api->group(['prefix' => 'services'], function ($api) {
                    $api->get('/find_by_number_and_estimate', 'ServiceController@find_by_number_and_estimate');

                    $api->group(['middleware' => 'user_can', 'collection' => 'services', 'action' => 'create'], function ($api) {
                        $api->post('/', 'ServiceController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'services', 'action' => 'read'], function ($api) {
                        $api->get('/', 'ServiceController@index');
                        $api->get('/{id}', 'ServiceController@show')->where('id', '[0-9]+');
                        $api->get('/by_company_is_group', 'ServiceController@by_company_is_group');
                        $api->get('/by_company', 'ServiceController@by_company');
                        $api->get('/by_client_contact', 'ServiceController@by_client_contact');
                        $api->get('/short', 'ServiceController@get_short');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'services', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'ServiceController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ServiceController@update')->where('id', '[0-9]+');
                        $api->post('/{id}/set_new_state', 'ServiceController@set_new_state')->where('id', '[0-9]+');
                        $api->post('/{id}/attachment', 'ServiceController@attachment')->where('id', '[0-9]+');
                        $api->put('/{id}/change_status', 'ServiceController@change_status')->where('id', '[0-9]+');
                        $api->post('/{id}/send_to_platform', 'ServiceController@send_to_platform')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'services', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'ServiceController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['middleware' => 'is_admin'], function ($api) {
                    $api->post('/services/settings', 'ServiceSettingsController@save');
                });
                // NOT RESTFULL
                $api->get('/services/export', 'ServiceController@export');
                $api->post('/services/additional', 'ServiceController@additional');
                $api->post('/services/{id}/remove_attachment', 'ServiceController@remove_attachment')->where('id', '[0-9]+');

                $api->post('/services/{id}/enable_access', 'ServiceChatController@enable_access');
                $api->post('/services/{id}/generate_new_link', 'ServiceChatController@generate_new_link');
                $api->post('/services/{id}/save_access', 'ServiceChatController@save');
                $api->get('/services/{id}/get_members', 'ServiceChatController@get');
            });
        });
    });
});
