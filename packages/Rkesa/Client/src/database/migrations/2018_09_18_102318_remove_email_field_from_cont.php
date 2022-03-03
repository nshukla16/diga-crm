<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactEmail;

class RemoveEmailFieldFromCont extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $contacts = ClientContact::all();
        if ($contacts){
            foreach($contacts as $contact){
                $email = new ClientContactEmail;
                $email->email = $contact->email;
                $email->client_contact_id = $contact->id;
                $email->save();
            }
        }
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->dropColumn(['email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_contacts', function (Blueprint $table) {
            $table->string('email');
        });
        $contacts = ClientContact::all();
        if ($contacts){
            foreach($contacts as $contact){
                $contact->email = implode(", ", array_map(function($e){
                    return $e['email'];
                }, $contact->client_contact_emails->toArray()));
                $contact->save();
            }
        }
    }
}
