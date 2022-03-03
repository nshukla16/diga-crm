<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\Estimate\Http\Controllers'], function ($api) {

            $api->get('/estimates/pdf/{id}', 'EstimateController@show_estimate')->where('id', '[0-9]+'); // signed url
            $api->get('/plannings/pdf/{id}', 'PlanningController@show_planning')->where('id', '[0-9]+'); // signed url

            $api->group(['middleware' => ['api_group'],], function ($api) {
                $api->group(['prefix' => 'estimates'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'estimates', 'action' => 'create'], function ($api) {
                        $api->post('/', 'EstimateController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'estimates', 'action' => 'read'], function ($api) {
                        $api->get('/', 'EstimateController@index');
                        $api->get('/{id}', 'EstimateController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'estimates', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'EstimateController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'EstimateController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'estimates', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'EstimateController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'estimate_units'], function ($api) {
                    $api->get('/', 'EstimateUnitsController@index');
                });

                $api->group(['prefix' => 'estimate_documents'], function ($api) {
                    $api->get('/', 'EstimateDocumentsController@index');
                });

                $api->group(['prefix' => 'ficha_resources'], function ($api) {
                    $api->get('/', 'FichaResourceController@index');
                });

                $api->group(['prefix' => 'fichas'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'fichas', 'action' => 'create'], function ($api) {
                        $api->post('/', 'FichaController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'fichas', 'action' => 'read'], function ($api) {
                        $api->get('/', 'FichaController@index');
                        $api->get('/{id}', 'FichaController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'fichas', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'FichaController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'FichaController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'fichas', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'FichaController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'resources'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'resources', 'action' => 'create'], function ($api) {
                        $api->post('/', 'ResourceController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'resources', 'action' => 'read'], function ($api) {
                        $api->get('/', 'ResourceController@index');
                        $api->get('/{id}', 'ResourceController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'resources', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'ResourceController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ResourceController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'resources', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'ResourceController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'plannings'], function ($api) {
                    $api->patch('/{id}', 'PlanningController@update')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'estimate_groups'], function ($api) {
                    $api->get('/', 'EstimateGroupController@all');
                    $api->get('/{id}', 'EstimateGroupController@show')->where('id', '[0-9]+');
                    $api->post('/', 'EstimateGroupController@store');
                    $api->put('/{id}', 'EstimateGroupController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'EstimateGroupController@update')->where('id', '[0-9]+');
                    $api->put('/change_status/{id}', 'EstimateGroupController@change_status')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'estimate_group_workers'], function ($api) {
                    $api->get('/', 'EstimateGroupWorkersController@index');
                    $api->get('by_estimate/', 'EstimateGroupWorkersController@by_estimate');
                    $api->get('my', 'EstimateGroupWorkersController@my');
                    $api->post('/', 'EstimateGroupWorkersController@update');
                });

                $api->group(['prefix' => 'estimate_group_material_consumption'], function ($api) {
                    $api->get('/', 'EstimateGroupMaterialConsumptionController@index');
                    $api->get('by_estimate/', 'EstimateGroupMaterialConsumptionController@by_estimate');
                    $api->post('/', 'EstimateGroupMaterialConsumptionController@update');
                });

                $api->group(['prefix' => 'estimate_line_categories'], function ($api) {
                    $api->get('/', 'EstimateLineCategoryController@index');
                });

                $api->group(['prefix' => 'estimate_resources'], function ($api) {
                    $api->get('/', 'EstimateResourcesController@index');
                });

                $api->group(['prefix' => 'estimate_line_fichas'], function ($api) {
                    $api->get('/', 'EstimateLineFichasController@index');
                });

                $api->group(['prefix' => 'estimate_group_worker_activities'], function ($api) {
                    $api->post('/', 'EstimateGroupWorkerActivityController@store');
                    $api->put('/{id}', 'EstimateGroupWorkerActivityController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'EstimateGroupWorkerActivityController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'EstimateGroupWorkerActivityController@destroy')->where('id', '[0-9]+');
                });

                // NOT RESTFULL
                $api->group(['middleware' => 'is_admin'], function ($api) {
                    $api->post('/settings/estimate', 'EstimateSettingsController@save');
                });

                $api->get('/estimates/export', 'EstimateController@export');

                $api->get('/fichas/search', 'FichaController@search');
                $api->get('/estimates/get_link/{id}', 'EstimateController@get_estimate_pdf_link')->where('id', '[0-9]+');
                $api->get('/plannings/get_link/{id}', 'PlanningController@get_planning_pdf_link')->where('id', '[0-9]+');
                $api->post('/estimates/set_master_estimate', 'EstimateController@set_master_estimate');
                $api->post('/estimates/{id}/block', 'EstimateController@block')->where('id', '[0-9]+');
                $api->get('/estimates/{id}/documents', 'EstimateController@documents')->where('id', '[0-9]+');

                $api->post('/estimate_pay_stages', 'EstimatePayStagesController@update');
                $api->get('/estimate_pay_stages/{id}', 'EstimatePayStagesController@show')->where('id', '[0-9]+');


                $api->get('/estimate_group_pay_stages/{estimate_id}', 'EstimateGroupController@index');

                $api->get('/financial_liabilities', 'FinancialLiabilitiesController@index');

                $api->post('/estimate_pay_stages/attach_invoice', 'EstimatePayStagesController@attach_invoice');
            });
        });
    });
});
