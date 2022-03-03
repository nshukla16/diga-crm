<?php

namespace Tests\Feature;

use App\Module;
use App\User;
use Carbon\Carbon;
use DateTime;
use Rkesa\Calendar\Models\Event;
use Rkesa\Calendar\Models\EventType;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Performance\Performance;

class EventPermissionsTest extends TestCase
{

    private $user1;
    private $user2;
    private $client;
    private $event1;
    private $event2;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();
        $this->user2 = self::create_test_user();
        $this->be($this->user1);

        $this->client = self::create_test_client();
        $this->data();

        $extended_cal = Module::where('name', 'calendar_extended')->first();
        $extended_cal->enabled = false;
        $extended_cal->save();
    }

    public function data()
    {
        $this->event1 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user1->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id]);
        $this->event2 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user2->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id]);
    }

    /** @test */
    public function event_create_create_permissions()
    {
        $this->user1->set_permission('events', 'create', 1); // allowed
        $this->get('/clients/'.$this->client->id.'/events/create')->assertRedirect('/');
    }

    /** @test */
    public function event_create_store_permissions()
    {
        $this->user1->set_permission('events', 'create', 0); // forbidden
        $this->post('/clients/'.$this->client->id.'/events')->assertStatus(403);

        $this->user1->set_permission('events', 'create', 1); // allowed
        $this->post('/clients/'.$this->client->id.'/events', ['event_type_id' => EventType::first()->id, 'start' => Carbon::now(), 'user_id' => User::first()->id])->assertStatus(200);
    }

    /** @test */
    public function event_read_through_client_permissions()
    {
        $this->user1->set_permission('events', 'read', 0); // forbidden
        $this->assertEmpty($this->client->main_contact()->events);

        $this->user1->set_permission('events', 'read', 1); // if responsible
        $this->client->main_contact()->load('events');
        $this->assertTrue($this->client->main_contact()->events->contains($this->event1->id));

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('events', 'read', 2); // for group
        $this->client->main_contact()->load('events');
        $this->assertTrue($this->client->main_contact()->events->contains($this->event1->id));
        $this->assertTrue($this->client->main_contact()->events->contains($this->event2->id));
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('events', 'read', 3); // allowed
        $this->client->main_contact()->load('events');
        $this->assertTrue($this->client->main_contact()->events->contains($this->event1->id));
        $this->assertTrue($this->client->main_contact()->events->contains($this->event2->id));
    }

    /** @test */
    public function event_read_through_index_permissions()
    {
        $this->user1->set_permission('events', 'read', 0); // forbidden
        $this->get('/calendar')->assertRedirect('/');

        $this->user1->set_permission('events', 'read', 1); // if responsible
        $this->get('/calendar')->assertStatus(200);
        $this->json('GET', '/calendar?format=json&done=2')->assertJsonFragment(['id' => $this->event1->id])
                                                                ->assertJsonMissing(['id' => $this->event2->id]);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('events', 'read', 2); // for group
        $this->get('/calendar')->assertStatus(200);
        $this->json('GET', '/calendar?format=json&done=2')->assertJsonFragment(['id' => $this->event1->id])
                                                                ->assertJsonFragment(['id' => $this->event2->id]);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('events', 'read', 3); // allowed
        $this->get('/calendar')->assertStatus(200);

        $this->json('GET', '/calendar?format=json&done=2&start='.(new DateTime())->format('Y-m-d').'&end='.(new DateTime('tomorrow'))->format('Y-m-d'))->assertJsonFragment(['id' => $this->event1->id])
                                                                ->assertJsonFragment(['id' => $this->event2->id]);
    }

    /** @test */
    public function event_read_show_permissions()
    {
        $this->user1->set_permission('events', 'read', 3); // allowed
        $this->get('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertRedirect('/');
    }

    /** @test */
    public function event_update_edit_permissions()
    {
        $this->user1->set_permission('events', 'update', 3); // allowed
        $this->get('/clients/'.$this->client->id.'/events/'.$this->event1->id.'/edit')->assertRedirect('/');
    }

    /** @test */
    public function event_update_update_permissions()
    {
        $this->user1->set_permission('events', 'update', 0); // forbidden
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(403);
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event2->id)->assertStatus(403);

        $this->user1->set_permission('events', 'update', 1); // if responsible
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(200);
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event2->id)->assertStatus(403);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('events', 'update', 2); // for group
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(200);
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event2->id)->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('events', 'update', 3); // allowed
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(200);
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event2->id)->assertStatus(200);
    }

    /** @test */
    public function event_update_update_done_permissions()
    {
        $this->event1->done = true;
        $this->event1->save();

        // You can not change an event that is done
        $this->user1->set_permission('events', 'update', 3); // allowed
        $this->patch('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(403);
    }

    /** @test */
    public function event_update_finish_permissions()
    {
        $this->user1->set_permission('events', 'addit', 0); // forbidden
        $this->post('/calendar/'.$this->event1->id.'/finish')->assertStatus(403);
        $this->post('/calendar/'.$this->event2->id.'/finish')->assertStatus(403);

        $this->user1->set_permission('events', 'addit', 1); // if responsible
        $this->post('/calendar/'.$this->event1->id.'/finish')->assertStatus(200);
        $this->event1 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user1->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id]);
        $this->post('/calendar/'.$this->event2->id.'/finish')->assertStatus(403);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('events', 'addit', 2); // for group
        $this->post('/calendar/'.$this->event1->id.'/finish')->assertStatus(200);
        $this->event1 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user1->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id]);
        $this->post('/calendar/'.$this->event2->id.'/finish')->assertStatus(200);
        $this->event2 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user2->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id]);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('events', 'addit', 3); // allowed
        $this->post('/calendar/'.$this->event1->id.'/finish')->assertStatus(200);
        $this->post('/calendar/'.$this->event2->id.'/finish')->assertStatus(200);
    }

    /** @test */
    public function event_delete_permissions()
    {
        $this->user1->set_permission('events', 'delete', 0); // forbidden
        $this->delete('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(403);
        $this->delete('/clients/'.$this->client->id.'/events/'.$this->event2->id)->assertStatus(403);

        $this->user1->set_permission('events', 'delete', 1); // if responsible
        $this->delete('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(200);
        $this->delete('/clients/'.$this->client->id.'/events/'.$this->event2->id)->assertStatus(403);
        //
        $this->data();

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('events', 'delete', 2); // for group
        $this->delete('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(200);
        $this->delete('/clients/'.$this->client->id.'/events/'.$this->event2->id)->assertStatus(200);
        //
        $this->data();
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('events', 'delete', 3); // allowed
        $this->delete('/clients/'.$this->client->id.'/events/'.$this->event1->id)->assertStatus(200);
        $this->delete('/clients/'.$this->client->id.'/events/'.$this->event2->id)->assertStatus(200);
    }

    /** @test */
    public function event_update_change_for_group_permissions()
    {
        $extended_cal = Module::where('name', 'calendar_extended')->first();
        $extended_cal->enabled = true;
        $extended_cal->save();

        $ev1 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user1->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id, 'event_group_id' => 1]);
        $ev2 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user2->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id, 'event_group_id' => 1]);

        $this->user1->set_permission('events', 'update', 0); // forbidden
        $this->post('/calendar/change_for_group', ['group_id' => 1, 'group_date' => Carbon::now()->format('Y-m-d'), 'event_type_id' => 2])->assertStatus(403);

        $this->user1->set_permission('events', 'update', 1); // if responsible
        $this->post('/calendar/change_for_group', ['group_id' => 1, 'group_date' => Carbon::now()->format('Y-m-d'), 'event_type_id' => 2])->assertStatus(200);
        $ev1 = Event::find($ev1->id);
        $ev2 = Event::find($ev2->id);
        $this->assertTrue($ev1->event_type_id == 2);
        $this->assertFalse($ev2->event_type_id == 2);

        $ev1 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user1->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id, 'event_group_id' => 1]);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('events', 'update', 2); // for group
        $this->post('/calendar/change_for_group', ['group_id' => 1, 'group_date' => Carbon::now()->format('Y-m-d'), 'event_type_id' => 2])->assertStatus(200);
        $ev1 = Event::find($ev1->id);
        $ev2 = Event::find($ev2->id);
        $this->assertTrue($ev1->event_type_id == 2);
        $this->assertTrue($ev2->event_type_id == 2);
        self::make_users_group($this->user1, $this->user2, false);

        $ev1 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user1->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id, 'event_group_id' => 1]);
        $ev2 = Event::create(['client_contact_id' => $this->client->main_contact()->id, 'user_id' => $this->user2->id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id, 'event_group_id' => 1]);

        $this->user1->set_permission('events', 'update', 3); // allowed
        $this->post('/calendar/change_for_group', ['group_id' => 1, 'group_date' => Carbon::now()->format('Y-m-d'), 'event_type_id' => 2])->assertStatus(200);
        $ev1 = Event::find($ev1->id);
        $ev2 = Event::find($ev2->id);
        $this->assertTrue($ev1->event_type_id == 2);
        $this->assertTrue($ev2->event_type_id == 2);
    }
}
