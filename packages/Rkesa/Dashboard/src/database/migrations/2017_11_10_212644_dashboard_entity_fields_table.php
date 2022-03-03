<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DashboardEntityFieldsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dashboard_entity_fields', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('type');
			$table->integer('dashboard_entity_id')->unsigned();
			$table->foreign('dashboard_entity_id')
				->references('id')->on('dashboard_entities')
				->onDelete('cascade');
			$table->integer('event_type_id')->nullable();
			$table->index('dashboard_entity_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('dashboard_entity_fields');
	}
}
