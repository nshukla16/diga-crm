<?php

namespace Tests\Feature;

use Rkesa\Estimate\Models\Resource;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ResourcesPermissionsTest extends TestCase
{
    private $user1;
    private $resource1;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();
        $this->resource1 = Resource::create();

        $this->be($this->user1);
    }

    /** @test */
    public function resources_create_create_permissions()
    {
        $this->user1->set_permission('resources', 'create', 0); // forbidden
        $this->get('/resources/create')->assertRedirect('/');

        $this->user1->set_permission('resources', 'create', 1); // allowed
        $this->get('/resources/create')->assertStatus(200);
    }

    /** @test */
    public function resources_create_store_permissions()
    {
        $this->user1->set_permission('resources', 'create', 0); // forbidden
        $this->post('/resources')->assertStatus(403);

        $this->user1->set_permission('resources', 'create', 1); // allowed
        $this->post('/resources')->assertStatus(200);
    }

    /** @test */
    public function resources_read_index_permissions()
    {
        $this->user1->set_permission('resources', 'read', 0); // forbidden
        $this->get(route('resources.index'))->assertRedirect('/');

        $this->user1->set_permission('resources', 'read', 1); // allowed
        $this->get(route('resources.index'))->assertStatus(200);

        $this->json('GET', '/resources?format=json')->assertJsonFragment(['id' => $this->resource1->id]);
    }

    /** @test */
    public function resources_read_show_permissions()
    {
        $this->user1->set_permission('resources', 'read', 1);
        $this->get('/resources/'.$this->resource1->id)->assertRedirect('/');
    }

    /** @test */
    public function resources_update_edit_permissions()
    {
        $this->user1->set_permission('resources', 'update', 0); // forbidden
        $this->get('/resources/'.$this->resource1->id.'/edit')->assertRedirect('/');

        $this->user1->set_permission('resources', 'update', 1); // allowed
        $this->get('/resources/'.$this->resource1->id.'/edit')->assertStatus(200);
    }

    /** @test */
    public function resources_update_update_permissions()
    {
        $this->user1->set_permission('resources', 'update', 0); // forbidden
        $this->patch('/resources/'.$this->resource1->id)->assertStatus(403);

        $this->user1->set_permission('resources', 'update', 1); // allowed
        $this->patch('/resources/'.$this->resource1->id)->assertStatus(200);
    }

    /** @test */
    public function resources_delete_permissions()
    {
        $this->user1->set_permission('resources', 'delete', 0); // forbidden
        $this->delete('/resources/'.$this->resource1->id)->assertStatus(403);

        $this->user1->set_permission('resources', 'delete', 1); // allowed
        $this->delete('/resources/'.$this->resource1->id)->assertStatus(200);
    }
}