<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\Hr\Http\Controllers'], function ($api) {

            $api->get('/users/pdf', 'HrController@blank'); // signed url
            $api->get('/users/pdf/{id}', 'HrController@card')->where('id', '[0-9]+'); // signed url

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'users'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'users', 'action' => 'create'], function ($api) {
                        $api->post('/', 'HrController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'users', 'action' => 'read'], function ($api) {
                        $api->get('/', 'HrController@index');
                        $api->get('/{id}', 'HrController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'users', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'HrController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'HrController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'users', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'HrController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'timetracker'], function ($api) {
                    $api->group(['middleware' => 'can_use_timetracker'], function ($api) {
                        $api->get('/personal', 'TimeTrackerController@personal');
                        $api->post('/employee', 'TimeTrackerController@employee');
                        $api->post('/checkpoint', 'TimeTrackerController@checkpoint');
                        $api->get('/logs', 'TimeTrackerController@logs');
                    });
                    $api->group(['middleware' => 'can_view_results_of_timetracker'], function ($api) {
                        $api->get('/totals', 'TimeTrackerController@index');
                        $api->post('/user_estimates', 'TimeTrackerController@user_estimates');
                        $api->post('/search', 'TimeTrackerController@search');
                        $api->post('/save_settings', 'TimeTrackerController@update_settings');
                    });
                });

                $api->group(['prefix' => 'kpi'], function ($api) {
                    $api->get('/periods', 'KpiPeriodController@index');
                    $api->get('/types', 'KpiTypeController@index');

                    $api->get('/users_and_groups', 'KpiUserAndGroupController@index');
                    $api->get('/users_and_groups/{id}', 'KpiUserAndGroupController@show')->where('id', '[0-9]+');
                    $api->post('/users_and_groups', 'KpiUserAndGroupController@store');
                    $api->patch('/users_and_groups/{id}', 'KpiUserAndGroupController@update')->where('id', '[0-9]+');
                    $api->delete('/users_and_groups/{id}', 'KpiUserAndGroupController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => '/hr/groups'], function ($api) {
                    $api->get('/', 'GroupsController@index');
                    $api->post('/', 'GroupsController@store');
                });

                $api->group(['prefix' => 'vacations'], function ($api) {
                    $api->get('/', 'VacationController@index');
                    $api->patch('/{id}', 'VacationController@update')->where('id', '[0-9]+');
                    $api->post('/', 'VacationController@store');
                    $api->delete('/{id}', 'VacationController@delete')->where('id', '[0-9]+');
                });

                // NOT RESTFULL
                $api->get('/users/all', 'UsersController@index');
                $api->get('/users/get_link/{id}', 'HrController@get_users_pdf_link')->where('id', '[0-9]+');
                $api->get('/user_blank/get_link', 'HrController@get_blank_pdf_link');
                $api->get('/users_and_groups/user/{id}', 'KpiUserAndGroupController@show_by_user_id')->where('id', '[0-9]+');
                $api->post('/users_and_groups/user/{id}/details', 'KpiUserAndGroupController@show_details_by_user_id')->where('id', '[0-9]+');

                $api->post('/users/change_password', 'HrController@change_password');

                $api->post('/checkin', 'TimeTrackerController@checkin');
                $api->get('/user/pin/{pin}', 'HrController@user_info_by_pin')->where('id', '[0-9]+');
                $api->get('/timetracker/report', 'TimeTrackerController@report');
            });
        });
    });
});
