<?php

namespace Tests\Feature;

use Rkesa\Estimate\Models\Resource;
use Tests\TestCase;
use Log;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TimetrackerPermissionsTest extends TestCase
{
    private $user1;

    public function setUp()
    {
        parent::setUp();

        $this->user1 = self::create_test_user();

        $this->be($this->user1);
    }

    private function make_user_can_use_timetracker($user, $val)
    {
        $user->can_use_timetracker = $val;
        $user->save();
    }

    private function make_user_can_view_results($user, $val)
    {
        $user->can_view_results_of_timetracker = $val;
        $user->save();
    }

    /** @test */
    public function timetracker_personal()
    {
        $this->make_user_can_use_timetracker($this->user1, false); // forbidden
        $this->get('/timetracker/personal')->assertRedirect('/');

        $this->make_user_can_use_timetracker($this->user1, true); // allow
        $this->get('/timetracker/personal')->assertStatus(200);
    }

    /** @test */
    public function timetracker_employee()
    {
        $this->make_user_can_use_timetracker($this->user1, false); // forbidden
        $this->post('/timetracker/employee')->assertStatus(403);

        $this->make_user_can_use_timetracker($this->user1, true); // allow
        $this->post('/timetracker/employee')->assertStatus(200);
    }

    /** @test */
    public function timetracker_checkpoint()
    {
        $this->make_user_can_use_timetracker($this->user1, false); // forbidden
        $this->post('/timetracker/checkpoint?lat=0&lng=0')->assertStatus(403);

        $this->make_user_can_use_timetracker($this->user1, true); // allow
        $this->post('/timetracker/checkpoint?lat=0&lng=0')->assertStatus(200);
    }

    /** @test */
    public function timetracker_totals()
    {
        $this->make_user_can_view_results($this->user1, false); // forbidden
        $this->get('/timetracker/totals')->assertRedirect('/');

        $this->make_user_can_view_results($this->user1, true); // allow
        $this->get('/timetracker/totals')->assertStatus(200);
    }

    /** @test */
    public function timetracker_user_estimates()
    {
        $this->make_user_can_view_results($this->user1, false); // forbidden
        $this->post('/timetracker/user_estimates')->assertStatus(403);

        $this->make_user_can_view_results($this->user1, true); // allow
        $this->post('/timetracker/user_estimates')->assertStatus(200);
    }

    /** @test */
    public function timetracker_search()
    {
        $this->make_user_can_view_results($this->user1, false); // forbidden
        $this->post('/timetracker/search')->assertStatus(403);

        $this->make_user_can_view_results($this->user1, true); // allow
        $this->post('/timetracker/search')->assertStatus(200);
    }
}