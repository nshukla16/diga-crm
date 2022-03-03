<?php

use App\GlobalSettings;
use App\Role;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SyncAdminPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $gs = GlobalSettings::first();
        if ($gs){
            $admins = User::where('is_admin', true)->get();
            foreach($admins as $admin){
                Role::where(['user_id' => $admin->id, 'action' => 'events'])->update(['addit' => 3]);
            }
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
