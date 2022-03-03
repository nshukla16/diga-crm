<?php

namespace Tests\Feature;

use App\GlobalSettings;
use Carbon\Carbon;
use Rkesa\Calendar\Models\Event;
use Rkesa\Calendar\Models\EventType;
use Rkesa\Client\Models\ClientHistory;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DontLoseClientsTest extends TestCase
{

    /** @test */
    public function check()
    {
//        $user1 = self::create_test_user();
//        $user2 = self::create_test_user();
//        $this->be($user1);
//
//        $client1 = self::create_test_client();
//
//        $gs = GlobalSettings::first();
//        $gs->check_clients_no_tasks = true;
//        $gs->save();
//
//        $user1->set_permission('clients', 'read', 3);
//
//        $client_route = route('clients.show', ['id' => $client1->id]);
//
//        $this->get('/home')->assertStatus(200);
//
//        // created manually and dont have any tasks
//        $client1->creator_user_id = $user1->id;
//        $client1->save();
//        $this->get('/')->assertRedirect($client_route);
//
//        // the last task was finished by a user
//        $this->be($user2);
//        $ev = Event::create(['client_contact_id' => $client1->main_contact()->id, 'user_id' => $user2->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id]);
//        $ev->done = true;
//        $ev->save();
//        $hist = ClientHistory::create(['type_id' => 18, 'client_contact_id' => $client1->main_contact()->id, 'event_id' => $ev->id]);
//        $this->get('/')->assertRedirect($client_route);
    }
}
