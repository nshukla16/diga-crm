<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\Expences\Http\Controllers'], function ($api) {

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'expences'], function ($api) {

                    $api->group(['middleware' => 'user_can', 'collection' => 'expences', 'action' => 'create'], function ($api) {
                        $api->post('/', 'ExpencesController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'expences', 'action' => 'read'], function ($api) {
                        $api->get('/', 'ExpencesController@index');
                        $api->get('/{id}', 'ExpencesController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'expences', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'ExpencesController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ExpencesController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'expences', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'ExpencesController@destroy')->where('id', '[0-9]+');
                    });
                });
            });
        });
    });
});
