<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\Email\Http\Controllers'], function ($api) {

            //

            $api->group(['middleware' => ['api_group'],], function ($api) {

                $api->group(['prefix' => 'domains'], function ($api) {
                    $api->group(['middleware' => 'is_admin'], function ($api) {
                        $api->get('/', 'DomainController@index');
                        $api->put('/', 'DomainController@update');
                        $api->patch('/', 'DomainController@update');
                    });
                });

                // NOT RESTFULL
                $api->post('/mail/action_email', 'EmailController@action_email');
                $api->post('/mail/login', 'EmailController@login');
            });
        });

    });
});
