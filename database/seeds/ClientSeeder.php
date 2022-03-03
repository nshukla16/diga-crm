<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($lang = 'ru')
    {
        DB::table('clients')->insert([
            [
                'id' => 1,
                'client_referrer_id' => 1,
                'vip' => false,
                'name' => 'Facebook Corporation',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'a_attributes' => '[]',
                'email' => 'info@facebook.com',
                'phone' => '000 000 000',
                'site' => 'www.facebook.com',
                'client_group' => 'Facebook, Inc.',
                'address_legal' => '1601 Willow Rd. Menlo Park, CA 94025'
            ],
        ]);
        DB::table('client_contacts')->insert([
            [
                'id' => 1,
                'client_id' => 1,
                'sex' => 1,
                'name' => 'Mark',
                'surname' => 'Zuckerberg',
                'contact_type' => 0,
                'is_main_contact' => true,
                'note' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'a_attributes' => '[]',
                'profession' => 'CEO',
                'client_referrer_id' => 1
            ],
            [
                'id' => 2,
                'client_id' => 1,
                'sex' => 0,
                'name' => 'Sheryl',
                'surname' => 'Kara Sandberg',
                'contact_type' => 0,
                'is_main_contact' => false,
                'note' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'a_attributes' => '[]',
                'profession' => 'COO',
                'client_referrer_id' => 1
            ],
        ]);
        DB::table('client_contact_phones')->insert([
            [
                'id' => 1,
                'client_contact_id' => 1,
                'phone_number' => '123 456 789',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'client_contact_id' => 2,
                'phone_number' => '234 567 891',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
        DB::table('client_contact_emails')->insert([
            [
                'id' => 1,
                'client_contact_id' => 1,
                'email' => 'mark@example.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'client_contact_id' => 2,
                'email' => 'sheryl@example.com',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
        DB::table('services')->insert([
            [
                'id' => 1,
                'client_contact_id' => 1,
                'responsible_user_id' => 1,
                'service_state_id' => 1,
                'service_priority_id' => 1,
                'aru_id' => null,
                'estimate_number' => '1-' . date("y"),
                'estimate_summ' => null,
                'address' => 'Facebook HQ - Campus Building - Menlo Park, California',
                'service_type_id' => 1,
                'name' => 'Office painting',
                'master_estimate_id' => null,
                'note' => '',
                'additional' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
        DB::table('events')->insert([
            [
                'id' => 1,
                'done' => 1,
                'user_id' => 1,
                'creator_user_id' => 1,
                'client_contact_id' => 1,
                'start' => Carbon::now(),
                'event_type_id' => 1,
                'service_id' => 1,
                'description' => '',
                'show_notification' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'done' => 0,
                'user_id' => 1,
                'creator_user_id' => 1,
                'client_contact_id' => 1,
                'start' => Carbon::now(),
                'event_type_id' => 2,
                'service_id' => 1,
                'description' => '',
                'show_notification' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
        DB::table('client_history')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'type_id' => 1,
                'client_contact_id' => 1,
                'message' => trans('template.Test_comment', [], $lang),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
    }
}
