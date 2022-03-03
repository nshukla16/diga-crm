<?php

use App\Role;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductsRoleToAllUsers extends Migration
{
    public function up()
    {
        $users = User::all();
        foreach ($users as $user) {
            $role = new Role();
            $role->user_id = $user->id;
            $role->action = 'products';
            $role->create = 0;
            $role->read = 0;
            $role->update = 0;
            $role->delete = 0;
            $role->addit = 0;

            $role->save();
        }
    }

    public function down()
    {
        $roles = Role::where('action', 'products')->delete();
    }
}
