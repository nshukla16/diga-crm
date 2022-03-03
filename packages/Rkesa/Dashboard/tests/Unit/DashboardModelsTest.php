<?php

namespace Rkesa\Dashboard\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Rkesa\Dashboard\Models\Dashboard;
use Rkesa\Dashboard\Models\DashboardEntity;
use Rkesa\Dashboard\Models\DashboardEntityField;
use Rkesa\Dashboard\Models\DashboardWidget;
use Rkesa\Service\Models\ServiceState;

class DashboardModelsTest extends TestCase {

	use DatabaseTransactions;

	public function test_it_should_can_insert_with_all_relations() {
		$statuses = ServiceState::where('type','=', 0)->get();
		$this->assertTrue(factory(Dashboard::class)
		->create()
		->each(function($dashboard) use ($statuses) {
			foreach($statuses as $key => $status) {
				$dashboard->entities()->save(
					factory(DashboardEntity::class)->create([
						'service_state_id' => $status->id,
						'dashboard_id' => $dashboard->id
					])
				);
			}
			$dashboard->entities()->each(function($entity) {
				$entity->fields()->save(
					factory(DashboardEntityField::class)->create([
						'dashboard_entity_id' => $entity->id,
						'type' => 1
					])
				);				
			});
			$dashboard->widgets()->save(
				factory(DashboardWidget::class)->create([
					'widget_type' => 1,
					'dashboard_id' => $dashboard->id
				])
			);
		}));

		$dashboard = Dashboard::with('entities', 'entities.state', 'entities.fields')->latest()->get()[0];
		
		$this->assertEquals(count($dashboard->entities), $statuses->count());
		$this->assertEquals($dashboard->entities[0]->service_state_id, $statuses[0]->id);
	}
}