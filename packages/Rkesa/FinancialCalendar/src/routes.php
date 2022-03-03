<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function($api) {

        $api->group(['namespace' => 'Rkesa\FinancialCalendar\Http\Controllers'], function ($api) {

            $api->group(['middleware' => ['api_group'],], function ($api) {


                $api->group(['prefix' => 'payment_events'], function ($api) {
                    $api->get('/', 'PaymentEventController@index');
                    $api->post('/', 'PaymentEventController@store');
//                    $api->get('/{id}', 'PaymentEventController@show')->where('id', '[0-9]+');
//                    $api->put('/{id}', 'PaymentEventController@update')->where('id', '[0-9]+');
//                    $api->patch('/{id}', 'PaymentEventController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'PaymentEventController@destroy')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'PaymentEventController@update')->where('id', '[0-9]+');
                    $api->post('/disable_email_sending', 'PaymentEventController@disable_email_sending');
                });             
            });
        });
    });
});
