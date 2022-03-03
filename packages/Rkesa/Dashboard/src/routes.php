<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\Dashboard\Http\Controllers'], function ($api) {

            //

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'dashboards'], function ($api) {
                    $api->group(['middleware' => 'is_admin'], function ($api) {
                        $api->get('/', 'DashboardController@index');
                        $api->post('/', 'DashboardController@store');
                        $api->get('/{id}', 'DashboardController@show')->where('id', '[0-9]+');
                        $api->put('/{id}', 'DashboardController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'DashboardController@update')->where('id', '[0-9]+');
                        $api->delete('/{id}', 'DashboardController@destroy')->where('id', '[0-9]+');
                    });
                });

                // NOT RESTFULL
                $api->get('/dashboards/config', 'DashboardController@config');
            });
        });
    });
});
