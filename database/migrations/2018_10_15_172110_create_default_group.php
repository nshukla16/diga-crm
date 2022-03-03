<?php

use App\GlobalSettings;
use App\Group;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Service\Models\ServiceScope;

class CreateDefaultGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $gs = GlobalSettings::first();
        if ($gs) {
            Group::truncate();
            $g = Group::create(['name' => trans('template.Without_group', [], $gs->default_language), 'service_scope_id' => ServiceScope::first()->id]);
            User::query()->update(['group_id' => $g->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
