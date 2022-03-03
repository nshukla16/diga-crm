<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
*/

$factory->define(Rkesa\Dashboard\Models\Dashboard::class, function (Faker\Generator $faker) {
	return [
		'name' => $faker->name
	];
});

$factory->define(Rkesa\Dashboard\Models\DashboardEntity::class, function () { return []; });
$factory->define(Rkesa\Dashboard\Models\DashboardEntityField::class, function() { return []; });
$factory->define(Rkesa\Dashboard\Models\DashboardWidget::class, function() { return []; });

