<?php

use App\Site;
use Illuminate\Database\Seeder;
use Rkesa\Calendar\Models\Event;
use Rkesa\Calendar\Models\EventType;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Client\Models\ClientReferrer;
use Rkesa\Service\Models\Service;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactPhone;
use Rkesa\Estimate\Models\Estimate;
use App\User;
use Rkesa\Service\Models\ServicePriority;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Service\Models\ServiceType;

class TestDataSeeder extends Seeder
{
    const CLIENTS_COUNT = 500;
    const USERS_COUNT = 10;
    const MAX_CONTACTS_PER_CLIENT = 2;
    const MAX_PHONES_PER_CONTACT = 2;
    const MAX_SERVICES_PER_CLIENT = 2;
    const MAX_ESTIMATES_PER_SERVICE = 3;
    const MAX_PRICE = 5000;
    const MAX_EVENTS_PER_CLIENT = 5;
    const MAX_HISTORY_ENTITIES = 6;
    const SITES_COUNT = 3;

    private $faker;
    private $users = [];

    function __construct() {
        $this->faker = Faker\Factory::create();
    }

    public function run()
    {
        for($i = 0; $i < self::SITES_COUNT; $i++){
            self::generate_site();
        }

        $clients = [];

        for($i = 0; $i < self::USERS_COUNT; $i++){
            $this->users[] = self::generate_user();
        }

        for($i = 0; $i < self::CLIENTS_COUNT; $i++){
            $client = self::generate_client();
            $clients[] = $client;
            for($j = 0; $j < rand(1, self::MAX_CONTACTS_PER_CLIENT); $j++){
                $contact = self::generate_contact($client->id);
                for($k = 0; $k < rand(1, self::MAX_PHONES_PER_CONTACT); $k++){
                    self::generate_contact_phone($contact->id);
                }
            }
            if (isset($contact)){
                $contact->is_main_contact = true;
                $contact->save();
            }
            for($j = 0; $j < rand(0, self::MAX_SERVICES_PER_CLIENT); $j++){
                $service = self::generate_service($client->id);
                self::generate_estimates($service);
                $add_count = rand(0,2);
                for($k = 0; $k < $add_count; $k++){
                    $service = self::generate_service($client->id);
                    $service->additional = $k+1;
                    $service->save();
                    self::generate_estimates($service);
                }
            }
            for($j = 0; $j < rand(0, self::MAX_EVENTS_PER_CLIENT); $j++){
                self::generate_event($client->id);
            }
            for($j = 0; $j < rand(1, self::MAX_HISTORY_ENTITIES); $j++){
                self::generate_history_entity($client->id);
            }
        }

    }

    private function get_random_user(){
        $index = rand(0, self::USERS_COUNT - 1);
        return $this->users[$index];
    }

    private function generate_site()
    {
        $site = new Site;
        $site->domain = $this->faker->freeEmailDomain;
        $site->token = 'random';
        $site->save();
        return $site;
    }

    private function generate_user()
    {
        $user = new User;
        $user->name = $this->faker->firstName . " " . $this->faker->lastName;
        $user->email = $this->faker->email;
        $user->password = $this->faker->password;
        $user->role_id = rand(1, 7);
        $user->photo = '/img/no_profile_picture.png';
        $user->save();
        return $user;
    }

    private function generate_client()
    {
        $client = new Client;
        $client->client_referrer_id = ClientReferrer::inRandomOrder()->first()->id;
        $client->vip = rand(0, 1);
//            $client->note = $faker->text($maxNbChars = 100);
        $client->save();
        return $client;
    }

    private function generate_event($client_id)
    {
        $event = new Event;
        $event->done = rand(0, 1);
        $event->user_id = self::get_random_user()->id;
        $event->creator_user_id = self::get_random_user()->id;
        $event->client_id = $client_id;
        $event->start = $this->faker->dateTimeThisYear;
        $event->event_type_id = EventType::inRandomOrder()->first()->id;
        $random_service = Service::where('client_id', $client_id)->inRandomOrder()->first();
        if ($random_service){
            $event->service_id = $random_service->id;
        }
        $event->description = $this->faker->text($maxNbChars = 100);
        $event->show_notification = false;
        $event->save();
        return $event;
    }

    private function generate_history_entity($client_id)
    {
        $history_entity = new ClientHistory;
        $random_service = Service::where('client_id', $client_id)->inRandomOrder()->first();
        if ($random_service) {
            $history_entity->service_id = $random_service->id;
            $history_entity->service_state_id = ServiceState::inRandomOrder()->first()->id;
        }
        $history_entity->user_id = self::get_random_user()->id;
        $history_entity->client_id = $client_id;
        $history_entity->message = $this->faker->text($maxNbChars = 100);

        $history_entity->type_id = [1,2,3,4,5,18][rand(0, 5)];
        switch ($history_entity->type_id){
            case 5:
                $history_entity->site_id = Site::inRandomOrder()->first()->id;
//                $history_entity->service_attachment_id = null;
                break;
            case 18:
                $random_event = Event::where(['client_id' => $client_id, 'done' => true])->inRandomOrder()->first();
                if ($random_event == null) {
                    $random_event = self::generate_event($client_id);
                    $random_event->done = true;
                    $random_event->save();
                }
                $history_entity->event_id = $random_event->id;
                break;
        }
        $history_entity->save();
        return $history_entity;
    }

    private function generate_contact_phone($contact_id)
    {
        $phone = new ClientContactPhone;
        $phone->client_contact_id = $contact_id;
        $phone->phone_number = $this->faker->tollFreePhoneNumber;
        $phone->save();
        return $phone;
    }

    private function generate_service($client_id){
        $service = new Service;
        $service->client_id = $client_id;
        $service->responsible_user_id = self::get_random_user()->id;
        $service->service_state_id = ServiceState::where('type', 0)->inRandomOrder()->first()->id;
        $service->service_priority_id = ServicePriority::inRandomOrder()->first()->id;
        $service->aru_id = null; // Doesn't matter
        $service->generate_estimate_number();
        $service->estimate_summ = null;
        $service->address = $this->faker->address;
        $service->service_type_id = ServiceType::inRandomOrder()->first()->id;
        $service->name = $this->faker->text($maxNbChars = 30);
        $service->master_estimate_id = null;
        $service->note = $this->faker->text($maxNbChars = 100);
        $service->additional = null;
        $service->created_at = $this->faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now', $timezone = null);
        $service->save();
        return $service;
    }

    private function generate_estimates($service)
    {
        $estimates = [];
        $current_estimate_count = rand(0, self::MAX_ESTIMATES_PER_SERVICE);
        if ($current_estimate_count > 0) {
            $estimates[] = self::generate_estimate($service->id);
            $rev = null;
            $opt = null;
            for ($k = 0; $k < $current_estimate_count - 1; $k++) {
                $estimate = self::generate_estimate($service->id);
                $rev_or_opt = rand(0,1);
                if ($rev_or_opt == 0){
                    if ($rev == null){ $rev = 1; } else { $rev++; }
                    $estimate->revision = $rev;
                } else {
                    if ($opt == null){ $opt = 1; } else { $opt++; }
                    $estimate->option = $opt;
                }
                $estimate->save();
                $estimates[] = $estimate;
            }
            $master_estimate = $estimates[rand(0, $current_estimate_count - 1)];
            $service->master_estimate_id = $master_estimate->id;
            $service->estimate_summ = $master_estimate->price;
            $service->save();
        }
    }

    private function generate_estimate($service_id)
    {
        $estimate = new Estimate;
        $estimate->service_id = $service_id;
        $estimate->price = rand(1, self::MAX_PRICE);
        $estimate->save();
        return $estimate;
    }

    private function generate_contact($client_id)
    {
        $contact = new ClientContact;
        $contact->client_id = $client_id;
        $contact->sex = rand(0, 1);
        $contact->name = $this->faker->firstName($contact->sex ? 'male' : 'female');
        $contact->surname = $this->faker->lastName;
        $contact->email = $this->faker->email;
        $contact->contact_type = rand(0, 1);
        $contact->is_main_contact = false;
        $contact->note = $this->faker->text($maxNbChars = 100);
        $contact->save();
        return $contact;
    }
}