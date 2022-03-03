<?php

namespace Rkesa\Dashboard\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Rkesa\Dashboard\Models\Dashboard;
use Rkesa\Dashboard\Models\DashboardEntity;
use Rkesa\Dashboard\Models\DashboardEntityField;
use Rkesa\Dashboard\Models\DashboardWidget;
use Rkesa\Service\Models\ServiceState;
use App\User;

function res($status = false, $message = '', $errors = array(), $params = array() ) {
	$data = [
		'OK' => $status,
		'message' => $message
	];

	if(count($params))
		foreach($params as $key => $param)
			$data[$key] = $param;

	if(is_array($errors) && count($errors))
		$data['errors'] = $errors;

	return $data;
}

class DashboardStoreTest extends TestCase {

	use WithoutMiddleware;
	use DatabaseTransactions;

	public function __construct() {
		$this->faker = \Faker\Factory::create();
	}

	private function dummyEntitiesFields($wrong=false) {
		$fields = array();
		for($i=0; $i<$this->faker->numberBetween(1,5); $i++) {
			array_push($fields, [
				'type' => $this->faker->numberBetween(1,10)
			]);
		}
		return $fields;
	}

	private function dummyEntities($statuses) {
		$output = array();
	
		foreach($statuses as $status) {
			array_push($output, [
				'hide' => false,
				'service_state_id' => $status->id,
				'fields' => $this->dummyEntitiesFields()
			]); 
		}

		return $output;
	}

	private function dummyWidgets() {
		$output = array();
		$count = $this->faker->numberBetween(1,8);

		for($i=0; $i < $count; $i++) {
			array_push($output, [
				'widget_type' => $this->faker->numberBetween(1,4),
				'data_type' =>  $this->faker->numberBetween(1,30)
			]);
		}

		return array( 'output' => $output, 'count' => $count );
	}

	private function postAjax($params=[], $headers=[], $ajax=true) {
		if ($ajax) {
			$headers['HTTP_X-Requested-With'] = 'XMLHttpRequest';
			$headers['HTTP_CONTENT_TYPE'] = 'application/json';
			$headers['HTTP_ACCEPT'] = 'application/json';
		}
		return $this->post('/settings/dashboards', $params, $headers);
	}

	public function test_it_should_be_ajax() {
		$response = $this->postAjax([], [], false);
		$response->assertStatus(403);
	}	
	
	public function test_it_should_be_restful_and_with_valid_json() {
		$response = $this->postAjax();
		$response
		->assertStatus(422)
		->assertHeader('Content-type', 'application/json')
		->assertJson(res(false, 'invalid JSON'));
	}
	
	public function test_it_should_validate_dashboard_name() {
		$response = $this->postAjax(["name" => ""]);
		$response
		->assertStatus(422)
		->assertJson(res(false, 'invalid data',  [
			'name' => [
				'The name field is required.'
			]
		]));
	}
	
	public function test_it_should_validate_count_of_dashboard_entities() {
		$response = $this->postAjax([
			'name' => 'Test',
			'entities' => [ ['hide' => 1], ['hide' => 0] ],
			'widgets' => []
		]);
		$response->assertStatus(422);
	}
	
	public function test_it_should_correctly_store_dashboard_data() {
		$statuses = ServiceState::where('type','=', 0)->get();

		$d_entities = $this->dummyEntities($statuses);
		$d_widgets = $this->dummyWidgets();

		$response = $this->postAjax([
			'name' => 'Test',
			"entities" => $d_entities,
			'widgets' => $d_widgets['output']
		]);

		$dashboard = Dashboard::latest()->with('entities', 'entities.state', 'entities.fields','widgets')->first();

		$this->assertEquals($dashboard->entities()->count(), $statuses->count());
		$this->assertEquals($dashboard->widgets()->count(), $d_widgets['count']);

		$json = res(true, 'Dashobard succesfully saved.', [], [
			'dashboard' => [ 'id' => $dashboard->id ]
		]);

		$response
		->assertStatus(200)
		->assertJson($json);
	}
}