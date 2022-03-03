<?php

namespace Tests\Feature;

use Rkesa\Estimate\Models\Estimate;
use Rkesa\Service\Models\Service;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EstimatePermissionsTest extends TestCase
{
    private $user1;
    private $user2;
    private $service1;
    private $service2;
    private $service3;
    private $client;
    private $estimate1;
    private $estimate2;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();
        $this->user2 = self::create_test_user();
        $this->client = self::create_test_client();
        $this->service1 = Service::create(['client_contact_id' => $this->client->main_contact()->id, 'responsible_user_id' => $this->user1->id]);
        $this->service2 = Service::create(['client_contact_id' => $this->client->main_contact()->id, 'responsible_user_id' => $this->user1->id]);
        $this->service3 = Service::create(['client_contact_id' => $this->client->main_contact()->id, 'responsible_user_id' => $this->user1->id]);
        $this->estimate1 = Estimate::create(['service_id' => $this->service1->id, 'user_id' => $this->user1->id]);
        $this->estimate2 = Estimate::create(['service_id' => $this->service2->id, 'user_id' => $this->user2->id]);

        $this->be($this->user1);
    }

    /** @test */
    public function estimates_create_create_permissions()
    {
        $this->user1->set_permission('estimates', 'create', 0); // forbidden
        $this->get('/estimates/create?service_id='.$this->service3->id)->assertRedirect('/');

        $this->user1->set_permission('estimates', 'create', 1); // allowed
        $this->get('/estimates/create?service_id='.$this->service3->id)->assertStatus(200);
    }

    /** @test */
    public function estimates_create_store_permissions()
    {
        $this->user1->set_permission('estimates', 'create', 0); // forbidden
        $this->post('/estimates?service_id='.$this->service3->id)->assertStatus(403);

        $this->user1->set_permission('estimates', 'create', 1); // allowed
        $this->post('/estimates?service_id='.$this->service3->id)->assertStatus(200);
    }

    /** @test */
    public function estimates_read_index_permissions()
    {
        $this->user1->set_permission('estimates', 'read', 0); // forbidden
        $this->get(route('estimates.index'))->assertRedirect('/');

        $this->user1->set_permission('estimates', 'read', 1); // if responsible
        $this->get(route('estimates.index'))->assertStatus(200);
        $this->json('GET', '/estimates?format=json')->assertJsonFragment(['service_id' => $this->service1->id])
                                                                ->assertJsonMissing(['service_id' => $this->service2->id]);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('estimates', 'read', 2); // for group
        $this->get(route('estimates.index'))->assertStatus(200);
        $this->json('GET', '/estimates?format=json')->assertJsonFragment(['service_id' => $this->service1->id])
                                                                ->assertJsonFragment(['service_id' => $this->service2->id]);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('estimates', 'read', 3); // allowed
        $this->get(route('estimates.index'))->assertStatus(200);
    }

    /** @test */
    public function estimates_read_show_permissions()
    {
        $this->user1->set_permission('estimates', 'read', 0);
        $this->get(route('estimates.show', ['id' => $this->estimate1->id]))->assertRedirect('/');

        $this->user1->set_permission('estimates', 'read', 1);
        $this->get('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->get('/estimates/'.$this->estimate2->id)->assertRedirect('/');

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('estimates', 'read', 2);
        $this->get('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->get('/estimates/'.$this->estimate2->id)->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('estimates', 'read', 3);
        $this->get('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->get('/estimates/'.$this->estimate2->id)->assertStatus(200);
    }

    /** @test */
    public function estimates_update_edit_permissions()
    {
        $this->user1->set_permission('estimates', 'update', 0); // forbidden
        $this->get('/estimates/'.$this->estimate1->id.'/edit')->assertRedirect('/');

        $this->user1->set_permission('estimates', 'update', 1); // if responsible
        $this->get('/estimates/'.$this->estimate1->id.'/edit')->assertStatus(200);
        $this->get('/estimates/'.$this->estimate2->id.'/edit')->assertRedirect('/');

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('estimates', 'update', 2); // for group
        $this->get('/estimates/'.$this->estimate1->id.'/edit')->assertStatus(200);
        $this->get('/estimates/'.$this->estimate2->id.'/edit')->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('estimates', 'update', 3); // allowed
        $this->get('/estimates/'.$this->estimate1->id.'/edit')->assertStatus(200);
        $this->get('/estimates/'.$this->estimate2->id.'/edit')->assertStatus(200);
    }

    /** @test */
    public function estimates_update_update_permissions()
    {
        $this->user1->set_permission('estimates', 'update', 0); // forbidden
        $this->patch('/estimates/'.$this->estimate1->id)->assertStatus(403);

        $this->user1->set_permission('estimates', 'update', 1); // if responsible
        $this->patch('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->patch('/estimates/'.$this->estimate2->id)->assertStatus(403);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('estimates', 'update', 2); // for group
        $this->patch('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->patch('/estimates/'.$this->estimate2->id)->assertStatus(200);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('estimates', 'update', 3); // allowed
        $this->patch('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->patch('/estimates/'.$this->estimate2->id)->assertStatus(200);
    }

    /** @test */
    public function estimates_delete_permissions()
    {
        $this->user1->set_permission('estimates', 'delete', 0); // forbidden
        $this->delete('/estimates/'.$this->estimate1->id)->assertStatus(403);

        $this->user1->set_permission('estimates', 'delete', 1); // if responsible
        $this->delete('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->delete('/estimates/'.$this->estimate2->id)->assertStatus(403);
        //
        $this->estimate1 = Estimate::create(['service_id' => $this->service1->id, 'user_id' => $this->user1->id]);
        $this->estimate2 = Estimate::create(['service_id' => $this->service2->id, 'user_id' => $this->user2->id]);

        self::make_users_group($this->user1, $this->user2, true);
        $this->user1->set_permission('estimates', 'delete', 2); // for group
        $this->delete('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->delete('/estimates/'.$this->estimate2->id)->assertStatus(200);
        //
        $this->estimate1 = Estimate::create(['service_id' => $this->service1->id, 'user_id' => $this->user1->id]);
        $this->estimate2 = Estimate::create(['service_id' => $this->service2->id, 'user_id' => $this->user2->id]);
        self::make_users_group($this->user1, $this->user2, false);

        $this->user1->set_permission('estimates', 'delete', 3); // allowed
        $this->delete('/estimates/'.$this->estimate1->id)->assertStatus(200);
        $this->delete('/estimates/'.$this->estimate2->id)->assertStatus(200);
    }
}