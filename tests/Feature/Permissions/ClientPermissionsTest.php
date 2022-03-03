<?php

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use App\User;
use Log;

class ClientPermissionsTest extends TestCase
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
        $this->get(route('companies.create'))->assertRedirect('/'); // create action
        $this->post(route('companies.store'))->assertStatus(403); // store action

        $this->user1->set_permission('clients', 'create', 1); // allowed
        $this->get(route('companies.create'))->assertStatus(200); // create action
        $this->post(route('companies.store'))->assertStatus(200); // store action
    }

    /** @test */
    public function client_read_index_permissions()
    {
        $this->user1->set_permission('clients', 'read', 0); // forbidden
        $this->get(route('companies.index'))->assertRedirect('/');

        $this->user1->set_permission('clients', 'read', 1); // if responsible
        $this->get(route('companies.index'))->assertStatus(200);
        $this->json('GET', '/clients?format=json')->assertJsonFragment(['client_id' => $this->client1->id])
            ->assertJsonMissing(['client_id' => $this->client2->id])
            ->assertJsonFragment(['client_id' => $this->client3->id]);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'read', 2); // for group
        $this->get(route('companies.index'))->assertStatus(200);
        $this->json('GET', '/clients?format=json')->assertJsonFragment(['client_id' => $this->client1->id])
            ->assertJsonFragment(['client_id' => $this->client2->id])
            ->assertJsonFragment(['client_id' => $this->client3->id]);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'read', 3); // allowed
        $this->get(route('companies.index'))->assertStatus(200);
    }

    /** @test */
    public function client_read_show_permissions()
    {
        $this->user1->set_permission('clients', 'read', 0);
        $this->get(route('companies.show', ['id' => $this->client1->id]))->assertRedirect('/');

        $this->user1->set_permission('clients', 'read', 1);
        $this->get('/companies/'.$this->client1->id)->assertStatus(200);
        $this->get('/companies/'.$this->client2->id)->assertRedirect('/');
        $this->get('/companies/'.$this->client3->id)->assertStatus(200);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'read', 2);
        $this->get('/companies/'.$this->client1->id)->assertStatus(200);
        $this->get('/companies/'.$this->client2->id)->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'read', 3);
        $this->get('/companies/'.$this->client1->id)->assertStatus(200);
        $this->get('/companies/'.$this->client2->id)->assertStatus(200);
    }

    /** @test */
    public function client_read_card_permissions()
    {
        $this->user1->set_permission('clients', 'read', 0);
        $this->get('/companies/'.$this->client1->id.'/card')->assertRedirect('/');

        $this->user1->set_permission('clients', 'read', 1);
        $this->get('/companies/'.$this->client1->id.'/card')->assertStatus(200);
        $this->get('/companies/'.$this->client2->id.'/card')->assertRedirect('/');
        $this->get('/companies/'.$this->client3->id.'/card')->assertStatus(200);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'read', 2);
        $this->get('/companies/'.$this->client1->id.'/card')->assertStatus(200);
        $this->get('/companies/'.$this->client2->id.'/card')->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'read', 3);
        $this->get('/companies/'.$this->client1->id.'/card')->assertStatus(200);
        $this->get('/companies/'.$this->client2->id.'/card')->assertStatus(200);
    }

    /** @test */
    public function client_update_edit_permissions()
    {
        $this->user1->set_permission('clients', 'update', 0); // forbidden
        $this->get('/companies/'.$this->client1->id.'/edit')->assertRedirect('/');

        $this->user1->set_permission('clients', 'update', 1); // if responsible
        $this->get('/companies/'.$this->client1->id.'/edit')->assertStatus(200);
        $this->get('/companies/'.$this->client2->id.'/edit')->assertRedirect('/');
        $this->get('/companies/'.$this->client3->id.'/edit')->assertStatus(200);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'update', 2); // for group
        $this->get('/companies/'.$this->client1->id.'/edit')->assertStatus(200);
        $this->get('/companies/'.$this->client2->id.'/edit')->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'update', 3); // allowed
        $this->get('/companies/'.$this->client1->id.'/edit')->assertStatus(200);
        $this->get('/companies/'.$this->client2->id.'/edit')->assertStatus(200);
    }

    /** @test */
    public function client_update_update_permissions()
    {
        $this->user1->set_permission('clients', 'update', 0); // forbidden
        $this->patch('/companies/'.$this->client1->id)->assertStatus(403);

        $this->user1->set_permission('clients', 'update', 1); // if responsible
        $this->patch('/companies/'.$this->client1->id)->assertStatus(200);
        $this->patch('/companies/'.$this->client2->id)->assertStatus(403);
        $this->patch('/companies/'.$this->client3->id)->assertStatus(200);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'update', 2); // for group
        $this->patch('/companies/'.$this->client1->id)->assertStatus(200);
        $this->patch('/companies/'.$this->client2->id)->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'update', 3); // allowed
        $this->patch('/companies/'.$this->client1->id)->assertStatus(200);
        $this->patch('/companies/'.$this->client2->id)->assertStatus(200);
    }

    /** @test */
    public function client_delete_permissions()
    {
        $this->user1->set_permission('clients', 'delete', 0); // forbidden
        $this->delete('/companies/'.$this->client1->id)->assertStatus(403);

        $this->user1->set_permission('clients', 'delete', 1); // if responsible
        $this->delete('/companies/'.$this->client1->id)->assertStatus(200);
        $this->delete('/companies/'.$this->client2->id)->assertStatus(403);
        $this->delete('/companies/'.$this->client3->id)->assertStatus(200);
        //
        $this->client1 = self::create_test_client($this->user1->id, $this->user1->id);
        $this->client2 = self::create_test_client($this->user2->id, $this->user2->id);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('clients', 'delete', 2); // for group
        $this->delete('/companies/'.$this->client1->id)->assertStatus(200);
        $this->delete('/companies/'.$this->client2->id)->assertStatus(200);
        //
        $this->client1 = self::create_test_client($this->user1->id, $this->user1->id);
        $this->client2 = self::create_test_client($this->user2->id, $this->user2->id);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('clients', 'delete', 3); // allowed
        $this->delete('/companies/'.$this->client1->id)->assertStatus(200);
        $this->delete('/companies/'.$this->client2->id)->assertStatus(200);
    }
}
