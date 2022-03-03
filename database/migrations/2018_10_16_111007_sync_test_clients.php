<?php

use App\GlobalSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Calendar\Models\Event;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactEmail;
use Rkesa\Client\Models\ClientContactPhone;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\Service;

class SyncTestClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $gs = GlobalSettings::first();
        if ($gs){
            $contact = ClientContact::find(1);
            if ($contact && $contact->name == 'Mark'){
                $contact2 = ClientContact::find(2);
                if ($contact2 && $contact2->name == 'Sheryl'){
                    // Everything is ok
                }else{
                    $n_cc2 = ClientContact::create([
                        'client_id' => 1,
                        'sex' => 0,
                        'name' => 'Sheryl',
                        'surname' => 'Kara Sandberg',
                        'contact_type' => 0,
                        'is_main_contact' => false,
                        'note' => '',
                        'a_attributes' => [],
                        'profession' => 'COO'
                    ]);
                    $ccp2 = ClientContactPhone::create([
                        'client_contact_id' => $n_cc2->id,
                        'phone_number' => '234 567 891',
                    ]);
                    $cce2 = ClientContactEmail::create([
                        'client_contact_id' => $n_cc2->id,
                        'email' => 'sheryl@example.com',
                    ]);

                    if ($n_cc2->id != 2){
                        self::change_contact_id($n_cc2, 2);
                    }
                }
            }else{
                $c = Client::create([
                    'client_referrer_id' => 1,
                    'vip' => false,
                    'name' => 'Facebook Corporation',
                    'a_attributes' => [],
                    'email' => 'info@facebook.com',
                    'phone' => '000 000 000',
                    'site' => 'www.facebook.com',
                    'client_group' => 'Facebook, Inc.',
                    'address_legal' => '1601 Willow Rd. Menlo Park, CA 94025'
                ]);
                $cc1 = ClientContact::create([
                    'client_id' => $c->id,
                    'sex' => 1,
                    'name' => 'Mark',
                    'surname' => 'Zuckerberg',
                    'contact_type' => 0,
                    'is_main_contact' => true,
                    'note' => '',
                    'a_attributes' => [],
                    'profession' => 'CEO'
                ]);
                $cc2 = ClientContact::create([
                    'client_id' => $c->id,
                    'sex' => 0,
                    'name' => 'Sheryl',
                    'surname' => 'Kara Sandberg',
                    'contact_type' => 0,
                    'is_main_contact' => false,
                    'note' => '',
                    'a_attributes' => [],
                    'profession' => 'COO'
                ]);
                $ccp1 = ClientContactPhone::create([
                    'client_contact_id' => $cc1->id,
                    'phone_number' => '123 456 789',
                ]);
                $ccp2 = ClientContactPhone::create([
                    'client_contact_id' => $cc2->id,
                    'phone_number' => '234 567 891',
                ]);
                $cce1 = ClientContactEmail::create([
                    'client_contact_id' => $cc1->id,
                    'email' => 'mark@example.com',
                ]);
                $cce2 = ClientContactEmail::create([
                    'client_contact_id' => $cc2->id,
                    'email' => 'sheryl@example.com',
                ]);
                $s = Service::create([
                    'client_contact_id' => $cc1->id,
                    'responsible_user_id' => 1,
                    'service_state_id' => 1,
                    'service_priority_id' => 1,
                    'aru_id' => null,
                    'estimate_summ' => null,
                    'address' => 'Facebook HQ - Campus Building - Menlo Park, California',
                    'service_type_id' => 1,
                    'name' => 'Office painting',
                    'master_estimate_id' => null,
                    'note' => '',
                    'additional' => null,
                ]);
                $s->generate_estimate_number();
                $s->save();
                $e1 = Event::create([
                    'done' => 1,
                    'user_id' => 1,
                    'creator_user_id' => 1,
                    'client_contact_id' => $cc1->id,
                    'start' => Carbon::now(),
                    'event_type_id' => 1,
                    'service_id' => $s->id,
                    'description' => '',
                    'show_notification' => false,
                ]);
                $e2 = Event::create([
                    'done' => 0,
                    'user_id' => 1,
                    'creator_user_id' => 1,
                    'client_contact_id' => $cc1->id,
                    'start' => Carbon::now(),
                    'event_type_id' => 2,
                    'service_id' => $s->id,
                    'description' => '',
                    'show_notification' => false,
                ]);
                $ch = ClientHistory::create([
                    'user_id' => 1,
                    'type_id' => 1,
                    'client_contact_id' => $cc1->id,
                    'message' => trans('template.Test_comment', [], $gs->default_language),
                ]);

                if ($c->id != 1){
                    self::change_client_id($c, 1);
                }
                if ($cc1->id != 1){
                    self::change_contact_id($cc1, 1);
                }
                if ($cc2->id != 2){
                    self::change_contact_id($cc2, 2);
                }
            }
        }
    }

    private function change_client_id($client, $id)
    {
        $client_at_id = Client::find($id);
        if ($client_at_id){
            $last_client_id = Client::orderBy('id', 'desc')->first()->id;

            // to change x <-> y, you need to use z: z = x, x = y, y = z
            $x = $client->id;
            $y = $id;
            $z = $last_client_id + 1;
            self::change_client_id_without_checks($x, $z);
            self::change_client_id_without_checks($y, $x);
            self::change_client_id_without_checks($z, $y);
        }else{
            self::change_client_id_without_checks($client->id, $id);
        }
    }

    private function change_client_id_without_checks($old_id, $new_id)
    {
        Log::info('change client from id ='.$old_id.' to id = '.$new_id);
        ClientContact::where('client_id', $old_id)->update(['client_id' => $new_id]);

        Client::where('id', $old_id)->update(['id' => $new_id]);
    }

    private function change_contact_id($contact, $id)
    {
        $contact_at_id = ClientContact::find($id);
        if ($contact_at_id){
            $last_contact_id = ClientContact::orderBy('id', 'desc')->first()->id;

            // to change x <-> y, you need to use z: z = x, x = y, y = z
            $x = $contact->id;
            $y = $id;
            $z = $last_contact_id + 1;
            self::change_contact_id_without_checks($x, $z);
            self::change_contact_id_without_checks($y, $x);
            self::change_contact_id_without_checks($z, $y);
        }else{
            self::change_contact_id_without_checks($contact->id, $id);
        }
    }

    private function change_contact_id_without_checks($old_id, $new_id)
    {
        Log::info('change contact from id ='.$old_id.' to id = '.$new_id);
        ClientContactEmail::where('client_contact_id', $old_id)->update(['client_contact_id' => $new_id]);
        ClientContactPhone::where('client_contact_id', $old_id)->update(['client_contact_id' => $new_id]);
        Event::where('client_contact_id', $old_id)->update(['client_contact_id' => $new_id]);
        Service::where('client_contact_id', $old_id)->update(['client_contact_id' => $new_id]);
        ClientHistory::where('client_contact_id', $old_id)->update(['client_contact_id' => $new_id]);

        ClientContact::where('id', $old_id)->update(['id' => $new_id]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
