<?php

namespace Rkesa\Dashboard\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Rkesa\Dashboard\Models\DashboardWidgetType;

class WidgetTypeTest extends TestCase {

	use WithoutMiddleware;
	use DatabaseTransactions;

	public function test_it_should_have_to_be_seeded() {
		$this->assertEquals(count(config('dashboard.widgets')), DashboardWidgetType::get()->count());
	}
}