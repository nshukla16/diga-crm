<?php

use App\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;

class UpdateUsersProjectPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $project_roles = Role::where('action', 'projects')->get();
        foreach($project_roles as $role){
            $bin_read = sprintf( "%08d", decbin( $role->read ));
            $bin_update = sprintf( "%08d", decbin( $role->update ));

            $ship_read = $bin_read[2];
            $ship_update = $bin_update[2];
            
            $replaced_read = substr_replace($bin_read, '', 2, 1);
            $replaced_update = substr_replace($bin_update, '', 2, 1);

            $role->read = bindec($replaced_read);
            $role->update = bindec($replaced_update);
            
            $role->save();

            $ship_role = new Role;
            $ship_role->action = "shipping_orders";
            $ship_role->user_id = $role->user_id;
            $ship_role->create = $role->create;
            $ship_role->delete = $role->delete;
            $ship_role->export = $role->export;
            $ship_role->addit = $role->addit;
            $ship_role->read = $ship_read;
            $ship_role->update = $ship_update;

            $ship_role->save();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
