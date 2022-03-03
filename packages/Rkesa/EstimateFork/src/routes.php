<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\EstimateFork\Http\Controllers'], function ($api) {

            //

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'estimate_forks'], function ($api) {
                    $api->get('/', 'EstimateForkController@index');
                    $api->group(['middleware' => 'is_admin'], function ($api) {
                        $api->post('/', 'EstimateForkController@store');
                        $api->get('/{id}', 'EstimateForkController@show')->where('id', '[0-9]+');
                        $api->put('/{id}', 'EstimateForkController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'EstimateForkController@update')->where('id', '[0-9]+');
                        $api->delete('/{id}', 'EstimateForkController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['middleware' => 'user_can', 'collection' => 'forks', 'action' => 'create'], function ($api) {
                    $api->post('/estimates/{id}/generate_forks', 'EstimateForkController@generate_forks')->where('id', '[0-9]+');
                });
            });
        });

    });
});
