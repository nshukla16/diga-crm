<?php

namespace Tests\Feature;

use Rkesa\Estimate\Models\Resource;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserPermissionsTest extends TestCase
{
    private $user1;
    private $user2;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();
        $this->user2 = self::create_test_user();

        $this->be($this->user1);
    }

    /** @test */
    public function hr_create_create_permissions()
    {
        $this->user1->set_permission('users', 'create', 0); // forbidden
        $this->get('/hr/create')->assertRedirect('/');

        $this->user1->set_permission('users', 'create', 1); // allowed
        $this->get('/hr/create')->assertStatus(200);
    }

    /** @test */
    public function hr_create_store_permissions()
    {
        $this->user1->set_permission('users', 'create', 0); // forbidden
        $this->post('/hr', ['email' => 'test@test.com', 'name' => 'Test'])->assertStatus(403);

        $this->user1->set_permission('users', 'create', 1); // allowed
        $this->post('/hr', ['email' => 'test@test.com', 'name' => 'Test'])->assertStatus(200);
    }

//    /** @test */
//    public function resources_read_index_permissions()
//    {
//        $this->user1->set_permission('resources', 'read', 0); // forbidden
//        $this->get(route('resources.index'))->assertRedirect('/');
//
//        $this->user1->set_permission('resources', 'read', 1); // allowed
//        $this->get(route('resources.index'))->assertStatus(200);
//
//        $this->json('GET', '/resources?format=json')->assertJsonFragment(['id' => $this->resource1->id]);
//    }

    /** @test */
    public function hr_read_show_permissions()
    {
        $this->user1->set_permission('users', 'read', 2);
        $this->get('/hr/'.$this->user2->id)->assertRedirect('/');
    }

    /** @test */
    public function hr_update_card_permissions()
    {
        $this->user1->set_permission('users', 'update', 0);
        $this->get('/hr/'.$this->user2->id.'/card')->assertRedirect('/');

        $this->user1->set_permission('users', 'update', 1);
        $this->get('/hr/'.$this->user2->id.'/card')->assertStatus(200);
    }

    /** @test */
    public function hr_update_edit_permissions()
    {
        $this->user1->set_permission('users', 'update', 0); // forbidden
        $this->get('/hr/'.$this->user2->id.'/edit')->assertRedirect('/');

        $this->user1->set_permission('users', 'update', 1); // allowed
        $this->get('/hr/'.$this->user2->id.'/edit')->assertStatus(200);
    }

    /** @test */
    public function hr_update_update_permissions()
    {
        $this->user1->set_permission('users', 'update', 0); // forbidden
        $this->patch('/hr/'.$this->user2->id)->assertStatus(403);

        $this->user1->set_permission('users', 'update', 1); // allowed
        $this->patch('/hr/'.$this->user2->id)->assertStatus(200);
    }

    /** @test */
    public function hr_delete_permissions()
    {
        $this->user1->set_permission('users', 'delete', 0); // forbidden
        $this->delete('/hr/'.$this->user2->id)->assertStatus(403);

        $this->user1->set_permission('users', 'delete', 1); // allowed
        $this->delete('/hr/'.$this->user2->id)->assertStatus(200);
    }
}