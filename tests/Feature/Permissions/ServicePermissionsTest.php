<?php

namespace Tests\Feature;

use Rkesa\Service\Models\Service;
use Tests\TestCase;
use Log;

class ServicePermissionsTest extends TestCase
{
    private $user1;
    private $user2;
    private $client;
    private $service1;
    private $service2;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();
        $this->user2 = self::create_test_user();
        $this->be($this->user1);

        $this->client = self::create_test_client();
        $this->service1 = Service::create(['client_contact_id' => $this->client->main_contact()->id, 'responsible_user_id' => $this->user1->id]);
        $this->service2 = Service::create(['client_contact_id' => $this->client->main_contact()->id, 'responsible_user_id' => $this->user2->id]);
    }

    /** @test */
    public function service_create_permissions()
    {
        $this->user1->set_permission('services', 'create', 0); // forbidden
        $this->get('/services/create?contact_id='.$this->client->main_contact()->id)->assertRedirect('/'); // create action
        $this->post('/services')->assertStatus(403); // store action

        $this->user1->set_permission('services', 'create', 1); // allowed
        $this->get('/services/create?contact_id='.$this->client->main_contact()->id)->assertStatus(200); // create action
        $this->post('/services')->assertStatus(200); // store action
    }

    /** @test */
    public function service_read_index_permissions()
    {
        $this->user1->set_permission('services', 'read', 0); // forbidden
        $this->get(route('services.index'))->assertRedirect('/');

        $this->user1->set_permission('services', 'read', 1); // if responsible
        $this->get(route('services.index'))->assertStatus(200);
        $this->json('GET', '/services?format=json')->assertJsonFragment(['id' => $this->service1->id])
            ->assertJsonMissing(['id' => $this->service2->id]);


        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('services', 'read', 2); // for group
        $this->get(route('services.index'))->assertStatus(200);
        $this->json('GET', '/services?format=json')->assertJsonFragment(['id' => $this->service1->id])
            ->assertJsonFragment(['id' => $this->service2->id]);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('services', 'read', 3); // allowed
        $this->get(route('services.index'))->assertStatus(200);
    }

    /** @test */
    public function service_read_show_permissions()
    {
        $this->user1->set_permission('services', 'read', 3);
        $this->get('/services/'.$this->service1->id)->assertRedirect('/');
    }

    /** @test */
    public function service_read_through_client_permissions()
    {
        $this->user1->set_permission('services', 'read', 0); // forbidden
        $this->assertEmpty($this->client->main_contact()->services);

        $this->user1->set_permission('services', 'read', 1); // if responsible
        $this->client->main_contact()->load('services');
        $this->assertTrue($this->client->main_contact()->services->contains($this->service1->id));

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('services', 'read', 2); // for group
        $this->client->main_contact()->load('services');
        $this->assertTrue($this->client->main_contact()->services->contains($this->service1->id));
        $this->assertTrue($this->client->main_contact()->services->contains($this->service2->id));
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('services', 'read', 3); // allowed
        $this->client->main_contact()->load('services');
        $this->assertTrue($this->client->main_contact()->services->contains($this->service1->id));
        $this->assertTrue($this->client->main_contact()->services->contains($this->service2->id));
    }

    /** @test */
    public function service_update_edit_permissions()
    {
        $this->user1->set_permission('services', 'update', 0); // forbidden
        $this->get('/services/'.$this->service1->id.'/edit')->assertRedirect('/');
        $this->get('/services/'.$this->service2->id.'/edit')->assertRedirect('/');

        $this->user1->set_permission('services', 'update', 1); // if responsible
        $this->get('/services/'.$this->service1->id.'/edit')->assertStatus(200);
        $this->get('/services/'.$this->service2->id.'/edit')->assertRedirect('/');

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('services', 'update', 2); // for group
        $this->get('/services/'.$this->service1->id.'/edit')->assertStatus(200);
        $this->get('/services/'.$this->service2->id.'/edit')->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('services', 'update', 3); // allowed
        $this->get('/services/'.$this->service1->id.'/edit')->assertStatus(200);
        $this->get('/services/'.$this->service2->id.'/edit')->assertStatus(200);
    }

    /** @test */
    public function service_update_update_permissions()
    {
        $this->user1->set_permission('services', 'update', 0); // forbidden
        $this->patch('/services/'.$this->service1->id)->assertStatus(403);
        $this->patch('/services/'.$this->service2->id)->assertStatus(403);

        $this->user1->set_permission('services', 'update', 1); // if responsible
        $this->patch('/services/'.$this->service1->id)->assertStatus(200);
        $this->patch('/services/'.$this->service2->id)->assertStatus(403);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('services', 'update', 2); // for group
        $this->patch('/services/'.$this->service1->id)->assertStatus(200);
        $this->patch('/services/'.$this->service2->id)->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('services', 'update', 3); // allowed
        $this->patch('/services/'.$this->service1->id)->assertStatus(200);
        $this->patch('/services/'.$this->service2->id)->assertStatus(200);
    }

    /** @test */
    public function service_update_attachment_permissions()
    {
        $this->user1->set_permission('services', 'update', 0); // forbidden
        $this->post('/services/'.$this->service1->id.'/attachment')->assertStatus(403);
        $this->post('/services/'.$this->service2->id.'/attachment')->assertStatus(403);

        $this->user1->set_permission('services', 'update', 1); // if responsible
        $this->post('/services/'.$this->service1->id.'/attachment')->assertStatus(200);
        $this->post('/services/'.$this->service2->id.'/attachment')->assertStatus(403);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('services', 'update', 2); // for group
        $this->post('/services/'.$this->service1->id.'/attachment')->assertStatus(200);
        $this->post('/services/'.$this->service2->id.'/attachment')->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('services', 'update', 3); // allowed
        $this->post('/services/'.$this->service1->id.'/attachment')->assertStatus(200);
        $this->post('/services/'.$this->service2->id.'/attachment')->assertStatus(200);
    }

    /** @test */
    public function service_delete_permissions()
    {
        $this->user1->set_permission('services', 'delete', 0); // forbidden
        $this->delete('/services/'.$this->service1->id)->assertStatus(403);
        $this->delete('/services/'.$this->service2->id)->assertStatus(403);

        $this->user1->set_permission('services', 'delete', 1); // if responsible
        $this->delete('/services/'.$this->service1->id)->assertStatus(200);
        $this->delete('/services/'.$this->service2->id)->assertStatus(403);
        //
        $this->service1 = Service::create(['client_contact_id' => $this->client->main_contact()->id, 'responsible_user_id' => $this->user1->id]);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('services', 'delete', 2); // for group
        $this->delete('/services/'.$this->service1->id)->assertStatus(200);
        $this->delete('/services/'.$this->service2->id)->assertStatus(200);
        //
        $this->service1 = Service::create(['client_contact_id' => $this->client->main_contact()->id, 'responsible_user_id' => $this->user1->id]);
        $this->service2 = Service::create(['client_contact_id' => $this->client->main_contact()->id, 'responsible_user_id' => $this->user2->id]);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('services', 'delete', 3); // allowed
        $this->delete('/services/'.$this->service1->id)->assertStatus(200);
        $this->delete('/services/'.$this->service2->id)->assertStatus(200);
    }
}
