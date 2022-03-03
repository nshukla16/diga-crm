<?php

namespace Tests\Feature;

use Log;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClientContactPermissionsTest extends TestCase
{
    private $user1;
    private $user2;
    private $client1;
    private $client2;
    private $client3;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();
        $this->user2 = self::create_test_user();
        $this->be($this->user1);

        $this->client1 = self::create_test_client($this->user1->id, $this->user1->id);
        $this->client2 = self::create_test_client($this->user2->id, $this->user2->id);
        $this->client3 = self::create_test_client($this->user1->id, $this->user2->id);

        $this->user1->set_permission('events', 'read', 3);
        $this->user1->set_permission('services', 'read', 3);
    }

    /** @test */
    public function client_create_permissions()
    {
        $this->user1->set_permission('clients', 'create', 0); // forbidden
        $this->get(route('clients.create'))->assertRedirect('/'); // create action
        $this->post(route('clients.store'))->assertStatus(403); // store action

        $this->user1->set_permission('clients', 'create', 1); // allowed
        $this->get(route('clients.create'))->assertStatus(200); // create action
        $this->post(route('clients.store'))->assertStatus(200); // store action
    }

    /** @test */
    public function client_read_index_permissions()
    {
        $this->user1->set_permission('clients', 'read', 0); // forbidden
        $this->get(route('clients.index'))->assertRedirect('/');

        $this->user1->set_permission('clients', 'read', 1); // if responsible
        $this->get(route('clients.index'))->assertStatus(200);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'read', 2); // for group
        $this->get(route('clients.index'))->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'read', 3); // allowed
        $this->get(route('clients.index'))->assertStatus(200);
    }

    /** @test */
    public function client_read_show_permissions()
    {
        $this->user1->set_permission('clients', 'read', 0);
        $this->get('/clients/'.$this->client1->main_contact()->id)->assertRedirect('/');

        $this->user1->set_permission('clients', 'read', 1);
        $this->get('/clients/'.$this->client1->main_contact()->id)->assertStatus(200);
        $this->get('/clients/'.$this->client2->main_contact()->id)->assertRedirect('/');
        $this->get('/clients/'.$this->client3->main_contact()->id)->assertStatus(200);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'read', 2);
        $this->get('/clients/'.$this->client1->main_contact()->id)->assertStatus(200);
        $this->get('/clients/'.$this->client2->main_contact()->id)->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'read', 3);
        $this->get('/clients/'.$this->client1->main_contact()->id)->assertStatus(200);
        $this->get('/clients/'.$this->client2->main_contact()->id)->assertStatus(200);
    }

    /** @test */
    public function client_update_edit_permissions()
    {
        $this->user1->set_permission('clients', 'update', 0); // forbidden
        $this->get('/clients/' . $this->client1->main_contact()->id . '/edit')->assertRedirect('/');

        $this->user1->set_permission('clients', 'update', 1); // if responsible
        $this->get('/clients/' . $this->client1->main_contact()->id . '/edit')->assertStatus(200);
        $this->get('/clients/' . $this->client2->main_contact()->id . '/edit')->assertRedirect('/');
        $this->get('/clients/' . $this->client3->main_contact()->id . '/edit')->assertStatus(200);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'update', 2); // for group
        $this->get('/clients/' . $this->client1->main_contact()->id . '/edit')->assertStatus(200);
        $this->get('/clients/' . $this->client2->main_contact()->id . '/edit')->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'update', 3); // allowed
        $this->get('/clients/' . $this->client1->main_contact()->id . '/edit')->assertStatus(200);
        $this->get('/clients/' . $this->client2->main_contact()->id . '/edit')->assertStatus(200);

    }

    /** @test */
    public function client_update_update_permissions()
    {
        $this->user1->set_permission('clients', 'update', 0); // forbidden
        $this->patch('/clients/'.$this->client1->main_contact()->id)->assertStatus(403);

        $this->user1->set_permission('clients', 'update', 1); // if responsible
        $this->patch('/clients/'.$this->client1->main_contact()->id)->assertStatus(200);
        $this->patch('/clients/'.$this->client2->main_contact()->id)->assertStatus(403);
        $this->patch('/clients/'.$this->client3->main_contact()->id)->assertStatus(200);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'update', 2); // for group
        $this->patch('/clients/'.$this->client1->main_contact()->id)->assertStatus(200);
        $this->patch('/clients/'.$this->client2->main_contact()->id)->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'update', 3); // allowed
        $this->patch('/clients/'.$this->client1->main_contact()->id)->assertStatus(200);
        $this->patch('/clients/'.$this->client2->main_contact()->id)->assertStatus(200);
    }

//    /** @test */
//    public function client_update_set_main_permissions()
//    {
//        $this->user1->set_permission('clients', 'update', 0); // forbidden
//        $this->post('/contacts/'.$this->client1->not_main_contact()->id.'/set_main')->assertStatus(403);
//        $this->post('/contacts/'.$this->client2->not_main_contact()->id.'/set_main')->assertStatus(403);
//
//        $this->user1->set_permission('clients', 'update', 1); // if responsible
//        // this not work because not_main_contact has no any events or services with responsible set to current user
//        $this->post('/contacts/'.$this->client1->not_main_contact()->id.'/set_main')->assertStatus(200);
//        $this->post('/contacts/'.$this->client2->not_main_contact()->id.'/set_main')->assertStatus(403);
//        $this->post('/contacts/'.$this->client3->not_main_contact()->id.'/set_main')->assertStatus(200);
//
//        self::make_users_group($this->user1, $this->user2, true);
//        $this->user1->set_permission('clients', 'update', 2); // for group
//        $this->post('/contacts/'.$this->client1->not_main_contact()->id.'/set_main')->assertStatus(200);
//        $this->post('/contacts/'.$this->client2->not_main_contact()->id.'/set_main')->assertStatus(200);
//        self::make_users_group($this->user1, $this->user2, false);
//
//        $this->user1->set_permission('clients', 'update', 3); // allowed
//        $this->post('/contacts/'.$this->client1->not_main_contact()->id.'/set_main')->assertStatus(200);
//        $this->post('/contacts/'.$this->client2->not_main_contact()->id.'/set_main')->assertStatus(200);
//    }

//    /** @test */
//    public function client_update_save_note_permissions()
//    {
//        $this->user1->set_permission('clients', 'update', 0); // forbidden
//        $this->post('/clients/'.$this->client1->not_main_contact()->id.'/save_note')->assertStatus(403);
//        $this->post('/clients/'.$this->client2->not_main_contact()->id.'/save_note')->assertStatus(403);
//
//        $this->user1->set_permission('clients', 'update', 1); // if responsible
//        // this not work because not_main_contact has no any events or services with responsible set to current user
//        $this->post('/clients/'.$this->client1->not_main_contact()->id.'/save_note')->assertStatus(200);
//        $this->post('/clients/'.$this->client2->not_main_contact()->id.'/save_note')->assertStatus(403);
//        $this->post('/clients/'.$this->client3->not_main_contact()->id.'/save_note')->assertStatus(200);
//
//        self::make_users_group($this->user1, $this->user2, true);
//        $this->user1->set_permission('clients', 'update', 2); // for group
//        $this->post('/clients/'.$this->client1->not_main_contact()->id.'/save_note')->assertStatus(200);
//        $this->post('/clients/'.$this->client2->not_main_contact()->id.'/save_note')->assertStatus(200);
//        self::make_users_group($this->user1, $this->user2, false);
//
//        $this->user1->set_permission('clients', 'update', 3); // allowed
//        $this->post('/clients/'.$this->client1->not_main_contact()->id.'/save_note')->assertStatus(200);
//        $this->post('/clients/'.$this->client2->not_main_contact()->id.'/save_note')->assertStatus(200);
//    }

//    /** @test */
//    public function client_delete_permissions()
//    {
//        $this->user1->set_permission('clients', 'delete', 0); // forbidden
//        $this->delete('/clients/'.$this->client1->not_main_contact()->id)->assertStatus(403);
//
//        $this->user1->set_permission('clients', 'delete', 1); // if responsible
//        // this not work because not_main_contact has no any events or services with responsible set to current user
//        $this->delete('/clients/'.$this->client1->not_main_contact()->id)->assertStatus(200);
//        $this->delete('/clients/'.$this->client2->not_main_contact()->id)->assertStatus(403);
//        $this->delete('/clients/'.$this->client3->not_main_contact()->id)->assertStatus(200);
//        //
//        $this->client1 = self::create_test_client($this->user1->id, $this->user1->id);
//        $this->client2 = self::create_test_client($this->user2->id, $this->user2->id);
//
//        self::make_users_group($this->user1, $this->user2, true);
//        $this->user1->set_permission('clients', 'delete', 2); // for group
//        $this->delete('/clients/'.$this->client1->not_main_contact()->id)->assertStatus(200);
//        $this->delete('/clients/'.$this->client2->not_main_contact()->id)->assertStatus(200);
//        //
//        $this->client1 = self::create_test_client($this->user1->id, $this->user1->id);
//        $this->client2 = self::create_test_client($this->user2->id, $this->user2->id);
//        self::make_users_group($this->user1, $this->user2, false);
//
//        $this->user1->set_permission('clients', 'delete', 3); // allowed
//        $this->delete('/clients/'.$this->client1->not_main_contact()->id)->assertStatus(200);
//        $this->delete('/clients/'.$this->client2->not_main_contact()->id)->assertStatus(200);
//    }
}
