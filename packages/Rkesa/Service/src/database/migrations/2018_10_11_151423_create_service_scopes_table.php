<?php

use App\GlobalSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Service\Models\ServiceScope;
use Rkesa\Service\Models\ServiceState;

class CreateServiceScopesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('start_service_state_id');
            $table->integer('end_service_state_id');
            $table->string('name');
            $table->timestamps();
        });
        if (ServiceState::count() > 0){
            $first = ServiceState::orderBy('order', 'asc')->first();
            $last = ServiceState::orderBy('order', 'desc')->first();

            $gs = GlobalSettings::first();
            ServiceScope::create(['start_service_state_id' => $first->id, 'end_service_state_id' => $last->id, 'name' => trans('template.Main_scope', [], $gs->default_language)]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_scopes');
    }
}
