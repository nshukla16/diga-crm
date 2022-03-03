<?php

namespace Tests\Feature;

use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Service\Models\Service;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FichasPermissionsTest extends TestCase
{
    private $user1;
    private $ficha1;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();
        $this->ficha1 = EstimateLineFicha::create(['is_pattern' => true]);

        $this->be($this->user1);
    }

    /** @test */
    public function fichas_create_create_permissions()
    {
        $this->user1->set_permission('fichas', 'create', 0); // forbidden
        $this->get('/fichas/create')->assertRedirect('/');

        $this->user1->set_permission('fichas', 'create', 1); // allowed
        $this->get('/fichas/create')->assertStatus(200);
    }

    /** @test */
    public function fichas_create_store_permissions()
    {
        $this->user1->set_permission('fichas', 'create', 0); // forbidden
        $this->post('/fichas')->assertStatus(403);

        $this->user1->set_permission('fichas', 'create', 1); // allowed
        $this->post('/fichas')->assertStatus(200);
    }

    /** @test */
    public function fichas_read_index_permissions()
    {
        $this->user1->set_permission('fichas', 'read', 0); // forbidden
        $this->get(route('fichas.index'))->assertRedirect('/');

        $this->user1->set_permission('fichas', 'read', 1); // allowed
        $this->get(route('fichas.index'))->assertStatus(200);

        $this->json('GET', '/fichas?format=json')->assertJsonFragment(['id' => $this->ficha1->id]);
    }

    /** @test */
    public function fichas_read_show_permissions()
    {
        $this->user1->set_permission('fichas', 'read', 1);
        $this->get('/fichas/'.$this->ficha1->id)->assertRedirect('/');
    }

    /** @test */
    public function fichas_update_edit_permissions()
    {
        $this->user1->set_permission('fichas', 'update', 0); // forbidden
        $this->get('/fichas/'.$this->ficha1->id.'/edit')->assertRedirect('/');

        $this->user1->set_permission('fichas', 'update', 1); // allowed
        $this->get('/fichas/'.$this->ficha1->id.'/edit')->assertStatus(200);
    }

    /** @test */
    public function fichas_update_update_permissions()
    {
        $this->user1->set_permission('fichas', 'update', 0); // forbidden
        $this->patch('/fichas/'.$this->ficha1->id)->assertStatus(403);

        $this->user1->set_permission('fichas', 'update', 3); // allowed
        $this->patch('/fichas/'.$this->ficha1->id)->assertStatus(200);
    }

    /** @test */
    public function fichas_delete_permissions()
    {
        $this->user1->set_permission('fichas', 'delete', 0); // forbidden
        $this->delete('/fichas/'.$this->ficha1->id)->assertStatus(403);

        $this->user1->set_permission('fichas', 'delete', 3); // allowed
        $this->delete('/fichas/'.$this->ficha1->id)->assertStatus(200);
    }
}