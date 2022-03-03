<?php

namespace App\Http\Controllers;

use App\Call3CX;
use App\Events\IncomingClient;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactPhone;
use SimpleXMLElement;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Storage;
use File;
use Rkesa\Calendar\Models\Event;
use Rkesa\Client\Models\ClientHistory;

class Ip3CXController extends Controller
{
    public function client_by_number(Request $request)
    {
        $number = $request['number'];

        $contact = ClientContact::with('client', 'client_contact_phones', 'client_contact_emails')->where(function ($c) use ($number) {
            $c->whereHas('client_contact_phones', function ($q) use ($number) {
                $q->where('phone_number', 'like', '%' . $number . '%');
            });
        })->first();

        if ($contact == null) {
            return response()->json(["result" => "error", "message" => "Not found"], 404, [], JSON_NUMERIC_CHECK);
        }

        $email = "";
        if ($contact->client_contact_emails != null && count($contact->client_contact_emails) > 0) {
            $email = $contact->client_contact_emails[0]->email;
        }

        $phone1 = "";
        $phone2 = "";
        $phone3 = "";
        $phone4 = "";
        if ($contact->client_contact_phones != null && count($contact->client_contact_phones) > 0) {
            $phone1 = $contact->client_contact_phones[0]->phone_number;
            if (count($contact->client_contact_phones) > 1) {
                $phone2 = $contact->client_contact_phones[1]->phone_number;
            }
            if (count($contact->client_contact_phones) > 2) {
                $phone3 = $contact->client_contact_phones[2]->phone_number;
            }
            if (count($contact->client_contact_phones) > 3) {
                $phone4 = $contact->client_contact_phones[3]->phone_number;
            }
        }

        return response()->json(["contact" => array(
            "id" => $contact->id,
            "firstname" => $contact->name,
            "lastname" => $contact->surname,
            "company" => $contact->client == null ? "" : $contact->client->name,
            "email" => $email,
            "businessphone" => $phone1,
            "businessphone2" => $phone2,
            "mobilephone" => $phone3,
            "mobilephone2" => $phone4,
            "url" => env('APP_URL') . "/contacts/" . $contact->id
        )], 200, []);
    }

    public function create_contact(Request $request)
    {
        $phone = $request['phone'];
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];

        $contact = new ClientContact();
        $contact->name = $first_name;
        $contact->surname = $last_name;
        $contact->save();

        $cphone = new ClientContactPhone();
        $cphone->phone_number = $phone;
        $cphone->client_contact_id = $contact->id;
        $cphone->save();

        $gs = GlobalSettings::first();

        $event = new Event;
        $event->start = (new DateTime())->getTimestamp();
        $event->user_id = $gs->responsible_user_id;
        $event->client_contact_id = $contact->id;
        $event->event_type_id = $gs->new_event_type_id;
        $event->done = false;
        $event->save();

        // $hist = new ClientHistory;
        // $hist->client_contact_id = $contact->id;
        // $hist->type_id = 25;
        // $hist->save();

        $contact->client_referrer;
        broadcast(new IncomingClient($contact->toArray()));

        return response()->json(["contact" => array(
            "id" => $contact->id,
            "firstname" => $contact->name,
            "lastname" => $contact->surname,
            "company" => "",
            "businessphone" => $cphone->phone_number,
            "businessphone2" => "",
            "mobilephone" => "",
            "mobilephone2" => "",
            "url" => env('APP_URL') . "/contacts/" . $contact->id
        )], 200, []);
    }

    public function create_call(Request $request)
    {
        $call = new Call3CX;
        $call->number = $request['number'];
        $call->call_type = $request['call_type'];
        $call->agent = $request['agent'];
        $call->duration = $request['duration'];
        $call->duration_minutes = $request['duration_minutes'];
        $call->duration_seconds = $request['duration_seconds'];
        $call->duration_milliseconds = $request['duration_milliseconds'];

        $client_contact_phone = ClientContactPhone::where('phone_number', 'like', '%' . $request['number'] . '%')->first();
        if ($client_contact_phone != null) {
            $call->client_contact_id = $client_contact_phone->client_contact_id;
        }

        $call->save();

        if ($call->client_contact_id > 0) {
            $hist = new ClientHistory;
            $hist->client_contact_id = $call->client_contact_id;
            switch ($call->call_type) {
                case "Inbound":
                    $hist->type_id = 31;
                    break;
                case "Outbound":
                    $hist->type_id = 32;
                    break;
                case "Missed":
                    $hist->type_id = 33;
                    break;
                case "Notanswered":
                    $hist->type_id = 34;
                    break;
            }
            $hist->save();
        }

        return response()->json(["contact" => $call], 200, []);
    }

    public function download_config(Request $request)
    {
        $data = File::get(storage_path("app/diga.xml"));;
        $file = 'diga.xml';
        $destinationPath = public_path() . "/upload/3cx/";
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $search = 'http://127.0.0.1';
        $replace = env('APP_URL');

        File::put($destinationPath . $file, str_replace($search, $replace, $data));
        return response()->download($destinationPath . $file);
    }
}
