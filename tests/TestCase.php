<?php
namespace Tests;

use App\GlobalSettings;
use App\Group;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Faker;
use Log;
use Illuminate\Support\Facades\Artisan;
use Rkesa\Calendar\Models\Event;
use Rkesa\Calendar\Models\EventType;
use Rkesa\Client\Models\Client;
use Rkesa\Service\Models\Service;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Rkesa\Service\Models\ServiceScope;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    public $faker;
    public $test_group;

    public function setUp()
    {
        parent::setUp();

        $gs = GlobalSettings::first();
        $gs->enable_companies = false;
        $gs->save();

        $this->test_group = Group::create(['name' => 'Test', 'service_scope_id' => ServiceScope::first()->id]);
    }

    function __construct() {
        parent::__construct();
        $this->faker = Faker\Factory::create();
    }

    public function create_test_client($event_responsible_id = null, $service_responsible_id = null)
    {
        $client = Client::create();
        $client->a_attributes = [];
        $client->save();
        $contact1 = $client->client_contacts()->create(['is_main_contact' => 1]);
        $contact1->a_attributes = [];
        $contact1->save();
        $contact2 = $client->client_contacts()->create(['is_main_contact' => 0]);
        $contact2->a_attributes = [];
        $contact2->save();
        if ($event_responsible_id != null) {
            Event::create(['client_contact_id' => $client->main_contact()->id, 'user_id' => $event_responsible_id, 'start' => Carbon::now(), 'event_type_id' => EventType::first()->id]);
        }
        if ($service_responsible_id != null) {
            Service::create(['client_contact_id' => $client->main_contact()->id, 'responsible_user_id' => $service_responsible_id]);
        }
        return $client;
    }

    public function create_test_user()
    {
        $user = User::create(['active' => true, 'email' => $this->faker->email, 'photo' => '/img/no_profile_picture.png', 'group_id' => 1]);
        $user->create_permissions();
        $user->set_admin(false);
        return $user;
    }

    public function make_users_group($user1, $user2, $same)
    {
        if ($same){
            $user1->update(['group_id' => $this->test_group->id]);
            $user2->update(['group_id' => $this->test_group->id]);
        }else{
            $user1->update(['group_id' => 1]);
            $user2->update(['group_id' => 1]);
        }

        $user1->load('groupmates');
        $user2->load('groupmates');
    }
}