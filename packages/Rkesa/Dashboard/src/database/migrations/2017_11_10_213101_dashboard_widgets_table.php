<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DashboardWidgetsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dashboard_widgets', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('widget_type');
			$table->integer('data_type')->nullable();
			$table->integer('service_state_id')->nullable();
			$table->integer('event_type_id')->nullable();
			$table->integer('dashboard_id')->unsigned();
			$table
				->foreign('dashboard_id')
      			->references('id')->on('dashboards')
  				->onDelete('cascade');
			$table->string('color1', 24)->nullable();
			$table->string('color2', 24)->nullable();
			$table->string('color3', 24)->nullable();
			$table->string('color4', 24)->nullable();
			$table->integer('size');
            // Indexes
			$table->index('service_state_id');
			$table->index('dashboard_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('dashboard_widgets');
	}
}
