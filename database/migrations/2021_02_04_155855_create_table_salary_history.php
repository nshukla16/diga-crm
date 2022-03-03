<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTableSalaryHistory extends Migration
{
    public function up()
    {
        Schema::create('user_salaries', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->boolean('salary_type');
            $table->float('amount');
            $table->date('start');
            $table->date('end')->nullable();

            $table->integer('user_id')->unsigned()->nullable();

            $table->foreign('user_id')->references('id')->on('users');
        });

        $users = User::all();
        foreach ($users as $user) {
            DB::table('user_salaries')->insert([
                ['salary_type' => $user->salary_type, 'amount' => $user->salary, 'start' => '2020-01-01', 'user_id' => $user->id]
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_salaries');
    }
}
