<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($lang = 'ru')
    {
        DB::table('groups')->insert([
            [
                'name' => trans('template.Without_group', [], $lang),
                'service_scope_id' => 1
            ],
        ]);
    }
}