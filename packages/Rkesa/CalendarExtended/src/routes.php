<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\CalendarExtended\Http\Controllers'], function ($api) {

            //

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'event_groups'], function ($api) {
                    $api->get('/', 'EventGroupsController@index');
                });

                // NOT RESTFULL
                $api->group(['middleware' => 'user_can', 'collection' => 'events', 'action' => 'update'], function ($api) {
                    $api->post('/calendar/change_for_group', 'CalendarExtendedController@change_for_group');
                });
            });
        });
    });
});
