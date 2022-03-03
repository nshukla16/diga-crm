<?php

namespace Tests\Feature;

use Rkesa\Estimate\Models\Estimate;
use Rkesa\Service\Models\Service;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlanningPermissionsTest extends TestCase
{
    private $user1;
    private $service1;
    private $estimate1;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();
        $this->service1 = Service::create();
        $this->estimate1 = Estimate::create(['service_id' => $this->service1->id]);

        $this->be($this->user1);
    }

    /** @test */
    public function plannings_create_create_permissions()
    {
        $this->user1->set_permission('plannings', 'create', 1);
        $this->get('/plannings/create')->assertRedirect('/');
    }

    /** @test */
    public function plannings_create_store_permissions()
    {
        $this->user1->set_permission('plannings', 'create', 1);
        $this->post('/plannings')->assertRedirect('/');
    }

    /** @test */
    public function plannings_read_index_permissions()
    {
        $this->user1->set_permission('plannings', 'read', 1);
        $this->get(route('plannings.index'))->assertRedirect('/');
    }

    /** @test */
    public function plannings_read_show_permissions()
    {
        $this->user1->set_permission('plannings', 'read', 0); // forbidden
        $this->get('/plannings/'.$this->estimate1->id.'/edit')->assertRedirect('/');

        $this->user1->set_permission('plannings', 'read', 1); // allowed
        $this->get('/plannings/'.$this->estimate1->id)->assertStatus(200);
    }

    /** @test */
    public function plannings_update_edit_permissions()
    {
        $this->user1->set_permission('plannings', 'update', 0); // forbidden
        $this->get('/plannings/'.$this->estimate1->id.'/edit')->assertRedirect('/');

        $this->user1->set_permission('plannings', 'update', 1); // allowed
        $this->get('/plannings/'.$this->estimate1->id.'/edit')->assertStatus(200);
    }

    /** @test */
    public function plannings_update_update_permissions()
    {
        $this->user1->set_permission('plannings', 'update', 0); // forbidden
        $this->patch('/plannings/'.$this->estimate1->id)->assertStatus(403);

        $this->user1->set_permission('plannings', 'update', 1); // allowed
        $this->patch('/plannings/'.$this->estimate1->id)->assertStatus(200);
    }

    /** @test */
    public function plannings_delete_permissions()
    {
        $this->user1->set_permission('plannings', 'delete', 1);
        $this->delete('/plannings/'.$this->estimate1->id)->assertRedirect('/');
    }
}