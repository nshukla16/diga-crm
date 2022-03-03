<?php

use App\Group;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHeadUserIdToGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->integer('head_user_id');
        });
        $groups = Group::all();
        foreach($groups as $group){
            $first_user = User::where('group_id', $group->id)->first();
            if ($first_user) {
                $group->head_user_id = $first_user->id;
                $group->save();
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
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('head_user_id');
        });
    }
}
