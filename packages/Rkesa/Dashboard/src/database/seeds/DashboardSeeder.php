<?php

namespace Rkesa\Dashboard\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Rkesa\Service\Models\ServiceState;
use App\User;

class DashboardSeeder extends Seeder {

	public function run($lang = 'ru') {
        switch ($lang) {
            case 'pt':
                DB::table('dashboards')->insert([
                    [
                        'id' => 1,
                        'name' => 'Principais indicadores'
                    ],
                ]);
                break;
            case 'ru':
                DB::table('dashboards')->insert([
                    [
                        'id' => 1,
                        'name' => 'Главные показатели'
                    ],
                ]);
                break;
            case 'en':
                DB::table('dashboards')->insert([
                    [
                        'id' => 1,
                        'name' => 'Main indicators'
                    ],
                ]);
                break;
        }
        DB::table('dashboard_widgets')->insert([

            [
                'widget_type' => 3,
                'data_type' => 1,
                'service_state_id' => null,
                'dashboard_id' => 1,
                'color1' => null, 'color2' => null, 'color3' => null, 'color4' => null,
                'size' => 1,
                'event_type_id' => null,
                'data' => null
            ],
            [
                'widget_type' => 3,
                'data_type' => 2,
                'service_state_id' => 8,
                'dashboard_id' => 1,
                'color1' => null, 'color2' => null, 'color3' => null, 'color4' => null,
                'size' => 2,
                'event_type_id' => null,
                'data' => null
            ],
            [
                'widget_type' => 4,
                'data_type' => 7,
                'service_state_id' => 7,
                'dashboard_id' => 1,
                'color1' => '#33af0e',
                'color2' => '#2f7bf5',
                'color3' => '#480c86',
                'color4' => '#232225',
                'size' => 1,
                'event_type_id' => 4,
                'data' => null
            ],
            [
                'widget_type' => 1,
                'data_type' => 3,
                'service_state_id' => 8,
                'dashboard_id' => 1,
                'color1' => null, 'color2' => null, 'color3' => null, 'color4' => null,
                'size' => 1,
                'event_type_id' => null,
                'data' => null
            ],
            [
                'widget_type' => 2,
                'data_type' => 5,
                'service_state_id' => 8,
                'dashboard_id' => 1,
                'color1' => null, 'color2' => null, 'color3' => null, 'color4' => null,
                'size' => 1,
                'event_type_id' => null,
                'data' => null
            ],
            [
                'widget_type' => 1,
                'data_type' => 4,
                'service_state_id' => 8,
                'dashboard_id' => 1,
                'color1' => null, 'color2' => null, 'color3' => null, 'color4' => null,
                'size' => 1,
                'event_type_id' => null,
                'data' => null
            ],
            [
                'widget_type' => 2,
                'data_type' => 6,
                'service_state_id' => 8,
                'dashboard_id' => 1,
                'color1' => null, 'color2' => null, 'color3' => null, 'color4' => null,
                'size' => 1,
                'event_type_id' => null,
                'data' => null
            ],
            [
                'widget_type' => 5,
                'data_type' => 9,
                'service_state_id' => null,
                'dashboard_id' => 1,
                'color1' => null, 'color2' => null, 'color3' => null, 'color4' => null,
                'size' => 2,
                'event_type_id' => null,
                'data' => '{"reject_state_id":12,"funnel_values":[{"id":1,"name":"Initial"},{"id":6,"name":"The visit is planned"},{"id":8,"name":"Budget sent"},{"id":9,"name":"Conversation"},{"id":13,"name":"Order cancelled"},{"id":14,"name":"Approved"}]}'
            ],
            [
                'widget_type' => 5,
                'data_type' => 10,
                'service_state_id' => null,
                'dashboard_id' => 1,
                'color1' => null, 'color2' => null, 'color3' => null, 'color4' => null,
                'size' => 2,
                'event_type_id' => null,
                'data' => '{"initial_state_id":1,"sale_state_id":14,"selected_columns":[{"id":1,"name":"Total services"},{"id":2,"name":"Total services in sale state"},{"id":3,"name":"Percent of sales"},{"id":4,"name":"Average time of decision"},{"id":5,"name":"Average check"},{"id":6,"name":"Total sum"},{"id":7,"name":"Average margin"},{"id":8,"name":"Number of second sales"}]}'
            ]
        ]);
        DB::table('dashboard_entities')->insert([
            [ 'hide' => 0, 'service_state_id' => 1, 'dashboard_id' => 1 ],
            [ 'hide' => 1, 'service_state_id' => 2, 'dashboard_id' => 1 ],
            [ 'hide' => 1, 'service_state_id' => 3, 'dashboard_id' => 1 ],
            [ 'hide' => 1, 'service_state_id' => 4, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 5, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 6, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 7, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 8, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 9, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 10, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 11, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 12, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 13, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 14, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 15, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 16, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 17, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 18, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 19, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 20, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 21, 'dashboard_id' => 1 ],
            [ 'hide' => 0, 'service_state_id' => 22, 'dashboard_id' => 1 ],
        ]);
        DB::table('dashboard_entity_fields')->insert([
            [ 'type' => 6, 'dashboard_entity_id' => 1, 'event_type_id' => null ],
            [ 'type' => 7, 'dashboard_entity_id' => 1, 'event_type_id' => null ],
            [ 'type' => 1, 'dashboard_entity_id' => 2, 'event_type_id' => null ],
            [ 'type' => 1, 'dashboard_entity_id' => 3, 'event_type_id' => null ],
            [ 'type' => 1, 'dashboard_entity_id' => 4, 'event_type_id' => null ],
            [ 'type' => 6, 'dashboard_entity_id' => 5, 'event_type_id' => null ],
            [ 'type' => 7, 'dashboard_entity_id' => 5, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 6, 'event_type_id' => null ],
            [ 'type' => 13, 'dashboard_entity_id' => 6, 'event_type_id' => 2 ],
            [ 'type' => 12, 'dashboard_entity_id' => 6, 'event_type_id' => 2 ],
            [ 'type' => 9, 'dashboard_entity_id' => 6, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 7, 'event_type_id' => null ],
            [ 'type' => 8, 'dashboard_entity_id' => 7, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 8, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 8, 'event_type_id' => null ],
            [ 'type' => 8, 'dashboard_entity_id' => 8, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 9, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 9, 'event_type_id' => null ],
            [ 'type' => 10, 'dashboard_entity_id' => 9, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 10, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 10, 'event_type_id' => null ],
            [ 'type' => 10, 'dashboard_entity_id' => 10, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 11, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 11, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 12, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 12, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 13, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 13, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 14, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 14, 'event_type_id' => null ],
            [ 'type' => 1, 'dashboard_entity_id' => 14, 'event_type_id' => null ],
            [ 'type' => 10, 'dashboard_entity_id' => 14, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 15, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 15, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 16, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 16, 'event_type_id' => null ],
            [ 'type' => 1, 'dashboard_entity_id' => 16, 'event_type_id' => null ],
            [ 'type' => 3, 'dashboard_entity_id' => 16, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 17, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 17, 'event_type_id' => null ],
            [ 'type' => 3, 'dashboard_entity_id' => 17, 'event_type_id' => null ],
            [ 'type' => 11, 'dashboard_entity_id' => 17, 'event_type_id' => null ],
            [ 'type' => 2, 'dashboard_entity_id' => 17, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 18, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 18, 'event_type_id' => null ],
            [ 'type' => 3, 'dashboard_entity_id' => 18, 'event_type_id' => null ],
            [ 'type' => 11, 'dashboard_entity_id' => 18, 'event_type_id' => null ],
            [ 'type' => 2, 'dashboard_entity_id' => 18, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 19, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 19, 'event_type_id' => null ],
            [ 'type' => 3, 'dashboard_entity_id' => 19, 'event_type_id' => null ],
            [ 'type' => 11, 'dashboard_entity_id' => 19, 'event_type_id' => null ],
            [ 'type' => 2, 'dashboard_entity_id' => 19, 'event_type_id' => null ],
            [ 'type' => 5, 'dashboard_entity_id' => 20, 'event_type_id' => null ],
            [ 'type' => 4, 'dashboard_entity_id' => 20, 'event_type_id' => null ],
            [ 'type' => 3, 'dashboard_entity_id' => 20, 'event_type_id' => null ],
            [ 'type' => 11, 'dashboard_entity_id' => 20, 'event_type_id' => null ],
            [ 'type' => 2, 'dashboard_entity_id' => 20, 'event_type_id' => null ],
        ]);


        $user = User::first();
        $user->dashboard_id = 1;
        $user->can_approve_vacations = true;
        $user->position = "Administrator";
        $user->can_enter_timesheet_and_consumption = true;
        $user->can_give_discount = true;
        $user->can_export = true;
        $user->can_see_financial_calendar = true;
        $user->can_see_kpi = true;
        $user->can_view_calls = true;
        $user->new_client_notifications = true;

        $user->save();

	}
}