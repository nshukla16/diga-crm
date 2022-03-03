<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;
use App\Role;

class AddLegalEntitiesPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (User::all() as $user){
            Role::create(['user_id' => $user->id, 'action' => 'legal_entities']);
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (User::all() as $user){
            Role::where(['user_id' => $user->id, 'action' => 'legal_entities'])->destroy();
        }
    }
}
