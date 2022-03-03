<?php

use App\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserProjectPermissions extends Migration
{
    public function up()
    {
        $project_roles = Role::where('action', 'projects')->get();
        foreach($project_roles as $role){
            $bin_read = sprintf( "%08d", decbin( $role->read ));
            $bin_update = sprintf( "%08d", decbin( $role->update ));

            //removing first 0 from 8 symbols length sequence
            $bin_read2 = substr($bin_read, 1); 
            $bin_update2 = substr($bin_update, 1); 

            $prev_val_read = substr($bin_read2, 3, 1); 
            $prev_val_update = substr($bin_update2, 3, 1); 
            
            //inserting 1 on the 3 position
            $replaced_read = substr_replace($bin_read2, $prev_val_read, 3, 0);
            $replaced_update = substr_replace($bin_update2, $prev_val_update, 3, 0);

            $role->read = bindec($replaced_read);
            $role->update = bindec($replaced_update);
            
            $role->save();

        }

    }

    public function down()
    {
    }
}
