<?php

namespace Tests\Feature;

use Rkesa\Estimate\Models\Estimate;
use Rkesa\Service\Models\Service;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EstimateForkPermissionsTest extends TestCase
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
    public function estimate_fork_create_permissions()
    {
        $this->user1->set_permission('forks', 'create', 0); // forbidden
        $this->post('/estimates/'.$this->estimate1->id.'/generate_forks')->assertStatus(403);

        $this->user1->set_permission('forks', 'create', 1); // allowed
        $this->post('/estimates/'.$this->estimate1->id.'/generate_forks')->assertStatus(200);
    }
}