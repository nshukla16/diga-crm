<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DashboardEntitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dashboard_entities', function (Blueprint $table) {
			$table->increments('id');
			$table->boolean('hide')->default(false);
			$table->integer('service_state_id');
			$table->integer('dashboard_id')->unsigned();
			$table->foreign('dashboard_id')->references('id')->on('dashboards')->onDelete('cascade');
			$table->index('dashboard_id');
			$table->index('service_state_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('dashboard_entities');
	}
}
