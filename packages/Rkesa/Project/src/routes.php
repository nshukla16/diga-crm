<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['middleware' => ['throttle:500,1', 'bindings']], function ($api) {

        $api->group(['namespace' => 'Rkesa\Project\Http\Controllers'], function ($api) {

            //

            $api->group(['middleware' => ['api_group'],], function ($api) {

                $api->group(['prefix' => 'projects'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'projects', 'action' => 'create'], function ($api) {
                        $api->post('/', 'ProjectController@store');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'projects', 'action' => 'read'], function ($api) {
                        $api->get('/', 'ProjectController@index');
                        $api->get('/{id}', 'ProjectController@show')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'projects', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'ProjectController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'ProjectController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'projects', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'ProjectController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'manufacturers'], function ($api) {
                    $api->get('/', 'ManufacturerController@index');
                    $api->post('/', 'ManufacturerController@store');
                    $api->get('/{id}', 'ManufacturerController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'ManufacturerController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ManufacturerController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'ManufacturerController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'equipments'], function ($api) {
                    $api->get('/', 'EquipmentController@index');
                    $api->post('/', 'EquipmentController@store');
                    $api->get('/{id}', 'EquipmentController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'EquipmentController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'EquipmentController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'EquipmentController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'specifications'], function ($api) {
                    $api->get('/', 'SpecificationController@index');
                    $api->post('/', 'SpecificationController@store');
                    $api->get('/{id}', 'SpecificationController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'SpecificationController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'SpecificationController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'SpecificationController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'carriers'], function ($api) {
                    $api->get('/', 'CarrierController@index');
                    $api->post('/', 'CarrierController@store');
                    $api->get('/{id}', 'CarrierController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'CarrierController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'CarrierController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'CarrierController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'carrier_contracts'], function ($api) {
                    $api->get('/', 'CarrierContractController@index');
                    $api->post('/', 'CarrierContractController@store');
                    $api->get('/{id}', 'CarrierContractController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'CarrierContractController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'CarrierContractController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'CarrierContractController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'manufacturer_contacts'], function ($api) {
                    $api->get('/', 'ManufacturerContactController@index');
                    $api->post('/', 'ManufacturerContactController@store');
                    $api->get('/{id}', 'ManufacturerContactController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'ManufacturerContactController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ManufacturerContactController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'ManufacturerContactController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'manufacturer_contracts'], function ($api) {
                    $api->get('/', 'ManufacturerContractController@index');
                    $api->post('/', 'ManufacturerContractController@store');
                    $api->get('/{id}', 'ManufacturerContractController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'ManufacturerContractController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ManufacturerContractController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'ManufacturerContractController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'client_equipments'], function ($api) {
                    $api->post('/', 'ClientEquipmentController@store');
                    $api->put('/{id}', 'ClientEquipmentController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ClientEquipmentController@update')->where('id', '[0-9]+');
                });

                // Orders

                $api->group(['prefix' => 'manufacturer_orders'], function ($api) {
                    // Exporting
                    $api->get('/export', 'ManufacturerOrderController@export');
                    $api->get('/', 'ManufacturerOrderController@index');
                    $api->post('/', 'ManufacturerOrderController@store');
                    $api->get('/{id}', 'ManufacturerOrderController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'ManufacturerOrderController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ManufacturerOrderController@update')->where('id', '[0-9]+');
                    $api->delete('/{id}', 'ManufacturerOrderController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'manufacturer_actual_orders'], function ($api) {
                    //                    $api->get('/', 'ManufacturerActualOrderController@index');
                    $api->post('/', 'ManufacturerActualOrderController@store');
                    //                    $api->get('/{id}', 'ManufacturerActualOrderController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'ManufacturerActualOrderController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'ManufacturerActualOrderController@update')->where('id', '[0-9]+');
                    //                    $api->delete('/{id}', 'ManufacturerActualOrderController@destroy')->where('id', '[0-9]+');
                });

                // Templates
                $api->get('/payment_invoice/export', 'TemplateController@payment_invoice');
                $api->get('/project/template', 'TemplateController@template');


                $api->group(['prefix' => 'project_notifications'], function ($api) {
                    $api->get('/', 'ProjectNotificationsController@index');
                });

                $api->group(['prefix' => 'project_autotasks'], function ($api) {
                    $api->get('/', 'ProjectAutotasksController@index');
                });

                $api->group(['prefix' => 'legal_entities'], function ($api) {
                    $api->group(['middleware' => 'user_can', 'collection' => 'legal_entities', 'action' => 'create'], function ($api) {
                        $api->post('/', 'LegalEntityController@store');
                    });

                    $api->get('/', 'LegalEntityController@index');
                    $api->get('/all', 'LegalEntityController@all');
                    $api->get('/{id}', 'LegalEntityController@show')->where('id', '[0-9]+');

                    $api->group(['middleware' => 'user_can', 'collection' => 'legal_entities', 'action' => 'update'], function ($api) {
                        $api->put('/{id}', 'LegalEntityController@update')->where('id', '[0-9]+');
                        $api->patch('/{id}', 'LegalEntityController@update')->where('id', '[0-9]+');
                    });
                    $api->group(['middleware' => 'user_can', 'collection' => 'legal_entities', 'action' => 'delete'], function ($api) {
                        $api->delete('/{id}', 'LegalEntityController@destroy')->where('id', '[0-9]+');
                    });
                });

                $api->group(['prefix' => 'legal_entity_contracts'], function ($api) {
                    //                    $api->get('/', 'LegalEntityContractController@index');
                    $api->post('/', 'LegalEntityContractController@store');
                    //                    $api->get('/{id}', 'LegalEntityContractController@show')->where('id', '[0-9]+');
                    $api->put('/{id}', 'LegalEntityContractController@update')->where('id', '[0-9]+');
                    $api->patch('/{id}', 'LegalEntityContractController@update')->where('id', '[0-9]+');
                    //                    $api->delete('/{id}', 'LegalEntityContractController@destroy')->where('id', '[0-9]+');
                });

                $api->group(['prefix' => 'project_types'], function ($api) {
                    $api->get('/', 'ProjectTypeController@index');
                });

                $api->group(['prefix' => 'project_statuses'], function ($api) {
                    $api->get('/', 'ProjectStatusController@index');
                });

                $api->get('/technical_documents', 'TechnicalDocumentController@index');

                // NOT RESTFULL
                $api->post('/settings/projects', 'ProjectSettingsController@save');
            });
        });
    });
});
