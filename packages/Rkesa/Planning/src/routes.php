<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\Planning\Http\Controllers'], function ($api) {

            //

            $api->group(['middleware' => ['api_group'],], function ($api) {

                $api->group(['prefix' => 'estimate_plannings'], function ($api) {
                    // General
                    $api->get('/', 'EstimatePlanningController@index');
                    $api->post('/', 'EstimatePlanningController@store');
                    $api->get('/{id}', 'EstimatePlanningController@show')->where('id', '[0-9]+');
//                    $api->put('/{id}', 'EstimatePlanningController@update')->where('id', '[0-9]+');
//                    $api->patch('/{id}', 'EstimatePlanningController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'EstimatePlanningController@destroy')->where('id', '[0-9]+');
                    $api->post('/{id}/update_task', 'EstimatePlanningController@update_task');
                    // Milestones
                    $api->get('/{id}/show_milestone', 'EstimatePlanningController@show_milestone');
                    $api->post('/store_milestone', 'EstimatePlanningController@store_milestone');
                    $api->post('/{id}/update_milestone', 'EstimatePlanningController@update_milestone');
                    $api->delete('/{id}/destroy_milestone', 'EstimatePlanningController@destroy_milestone')->where('id', '[0-9]+');
                    // Work hours
                    $api->post('/{id}/update_work_hours', 'EstimatePlanningController@update_work_hours');
                    $api->post('/{id}/export_excel', 'EstimatePlanningController@export_to_excel');
                });

                $api->group(['prefix' => 'user_plannings'], function ($api) {
                    $api->get('/', 'UserPlanningController@index');                    
                    $api->post('/', 'UserPlanningController@store');
                    $api->get('/{id}', 'UserPlanningController@show')->where('id', '[0-9]+');
//                    $api->put('/{id}', 'UserPlanningController@update')->where('id', '[0-9]+');
//                    $api->patch('/{id}', 'UserPlanningController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'UserPlanningController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'user_planning_users'], function ($api) {
//                    $api->get('/', 'UserPlanningUserController@index');
                    $api->post('/', 'UserPlanningUserController@store');
//                    $api->get('/{id}', 'UserPlanningUserController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'UserPlanningUserController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'UserPlanningUserController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'UserPlanningUserController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'user_planning_user_tasks'], function ($api) {
//                    $api->get('/', 'UserPlanningUserTaskController@index');
                    $api->get('/by_estimate', 'UserPlanningUserTaskController@by_estimate');
                    $api->post('/', 'UserPlanningUserTaskController@store');
//                    $api->get('/{id}', 'UserPlanningUserTaskController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'UserPlanningUserTaskController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'UserPlanningUserTaskController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'UserPlanningUserTaskController@destroy')->where('id', '[0-9]+');
                    $api->post('/inform_construction_managers', 'UserPlanningUserTaskController@inform_construction_managers');
                });

                $api->group(['prefix' => 'estimate_planning_lines'], function ($api) {
//                    $api->get('/', 'UserPlanningUserTaskController@index');
                    $api->post('/', 'EstimatePlanningLineController@store');
//                    $api->get('/{id}', 'EstimatePlanningLineController@show')->where('id', '[0-9]+');
//                    $api->put('/{id}', 'EstimatePlanningLineController@update')->where('id', '[0-9]+');
//                    $api->patch('/{id}', 'EstimatePlanningLineController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'EstimatePlanningLineController@destroy')->where('id', '[0-9]+');
                });

            });
        });
    });
});