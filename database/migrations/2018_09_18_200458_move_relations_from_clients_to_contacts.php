<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Calendar\Models\Event;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\Service;

class MoveRelationsFromClientsToContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function($table)
        {
            $table->renameColumn('client_id', 'client_contact_id');
        });
        foreach(Service::all() as $service){
            $cc = ClientContact::where(['client_id' => $service->client_contact_id, 'is_main_contact' => true])->first();
            $service->client_contact_id = $cc->id;
            $service->save();
        }
        Schema::table('events', function($table)
        {
            $table->renameColumn('client_id', 'client_contact_id');
        });
        foreach(Event::all() as $event){
            $cc = ClientContact::where(['client_id' => $event->client_contact_id, 'is_main_contact' => true])->first();
            $event->client_contact_id = $cc->id;
            $event->save();
        }
        Schema::table('client_history', function($table)
        {
            $table->renameColumn('client_id', 'client_contact_id');
        });
        foreach(ClientHistory::all() as $hist){
            $cc = ClientContact::where(['client_id' => $hist->client_contact_id, 'is_main_contact' => true])->first();
            $hist->client_contact_id = $cc->id;
            $hist->save();
        }
        foreach(Client::withCount('client_contacts as client_contacts_count')->get() as $client){
            if ($client->client_contacts_count == 1){
                $contact = $client->client_contacts->first();
                $contact->client_referrer_id = $client->client_referrer_id;
                $contact->referrer_note = $client->referrer_note;
                $contact->client_id = null;
                $contact->save();
                $client->delete();
            }else{
                $array_of_contact_names = array_map(
                    function($contact) { return $contact['name'].' '.$contact['surname']; },
                    $client->client_contacts->toArray()
                );
                $client->name = join(', ', $array_of_contact_names);
                $client->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function($table)
        {
            $table->renameColumn('client_contact_id', 'client_id');
        });
        foreach(Service::all() as $service){
            $cc = ClientContact::find($service->client_id);
            $service->client_id = $cc->client_id;
            $service->save();
        }
        Schema::table('events', function($table)
        {
            $table->renameColumn('client_contact_id', 'client_id');
        });
        foreach(Event::all() as $event){
            $cc = ClientContact::find($event->client_id);
            $event->client_id = $cc->client_id;
            $event->save();
        }
        Schema::table('client_history', function($table)
        {
            $table->renameColumn('client_contact_id', 'client_id');
        });
        foreach(ClientHistory::all() as $hist){
            $cc = ClientContact::find($hist->client_id);
            $hist->client_id = $cc->client_id;
            $hist->save();
        }
    }
}
