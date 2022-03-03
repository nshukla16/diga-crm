<?php

use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPinToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('pin')->nullable();
        });

        $users = User::all();
        foreach($users as $user){
            $pins = [];
            while(true){
                $pin = rand(1000,9999);
                if (in_array($pin, $pins)){
                    continue;
                }
                else{
                    $user->pin = $pin;
                    $user->save();
                    break;
                }
            }            
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
}
