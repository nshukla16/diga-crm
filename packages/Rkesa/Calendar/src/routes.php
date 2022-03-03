<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function($api) {

        $api->group(['namespace' => 'Rkesa\Calendar\Http\Controllers'], function ($api) {

            $api->get('/fills/pdf/{id}', 'ChecklistFilledController@show_fill')->where('id', '[0-9]+'); // signed url
            $api->get('/checklists/pdf/{id}', 'ChecklistController@show_checklist')->where('id', '[0-9]+'); // signed url

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'event_types'], function ($api) {
                    $api->get('/', 'EventTypesController@index');
                });

                $api->group(['prefix' => 'checklists'], function ($api) {
                    $api->get('/{id}', 'ChecklistController@show')->where('id', '[0-9]+');

                    $api->group(['middleware' => 'is_admin'], function ($api) {
                        $api->get('/', 'ChecklistController@index');
                        $api->post('/', 'ChecklistController@store');
                        $api->put('/{id}', 'ChecklistController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ChecklistController@update')->where('id', '[0-9]+');
                        $api->delete('/{id}', 'ChecklistController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'events'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'events', 'action' => 'create'], function ($api) {
                        $api->post('/', 'CalendarController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'events', 'action' => 'read'], function ($api) {
                        $api->get('/', 'CalendarController@index');
                        $api->get('/{id}', 'CalendarController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'events', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'CalendarController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'CalendarController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'events', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'CalendarController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'fills'], function ($api) {
                    $api->get('/', 'ChecklistFilledController@index');
                    $api->post('/', 'ChecklistFilledController@store');
                    $api->get('/{id}', 'ChecklistFilledController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'ChecklistFilledController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ChecklistFilledController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'ChecklistFilledController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['middleware' => 'is_admin'], function ($api) {
                    $api->post('/settings/calendar', 'CalendarSettingsController@save');
                });

                $api->group(['middleware' => 'user_can', 'collection' => 'events', 'action' => 'addit'], function ($api) {
                    $api->post('/calendar/{id}/finish', 'CalendarController@finish')->where('id', '[0-9]+');
                });
                // NOT RESTFULL
                $api->get('/fills/get_link/{id}', 'ChecklistFilledController@get_fill_pdf_link')->where('id', '[0-9]+');
                $api->get('/checklists/get_link/{id}', 'ChecklistController@get_checklist_pdf_link')->where('id', '[0-9]+');
                $api->post('/calendar/action_event', 'CalendarController@action_event');                
            });
        });
    });
});
