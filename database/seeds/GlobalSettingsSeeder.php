<?php

use Illuminate\Database\Seeder;

class GlobalSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($lang = 'ru')
    {
        DB::table('global_settings')->insert([
            [
                'site_name' => 'ERP',
                'site_logo' => '/img/logo.png',
                'background_image' => '/img/background.png',
                'last_estimate_number' => 1, // Because test user exists
                'unlocker_user_id' => 1, // Admin
                'responsible_user_id' => 1, // Admin
                'company_type' => 1, // Alpinismo Industrial
                'estimate_bottom_text' => '<div style="text-align: center;"><span class="page"></span> / <span class="topage"></div>',
                'estimate_conditions_text' => '',
                'estimate_show_contract' => false,
                'new_service_state_id' => 1,
                'add_service_state_id' => 1,
                'new_event_type_id' => 6,
                'default_language' => $lang,
                'contact_attributes' => '[]',
                'client_attributes' => '[]'
            ],
        ]);
    }
}
