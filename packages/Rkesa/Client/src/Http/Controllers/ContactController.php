<?php

namespace Rkesa\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookController;
use App\Http\Traits\ClientAndContactWithTrait;
use App\Http\Traits\MailTrait;
use App\Notifications\ContactAssigned;
use App\User as User;
use Illuminate\Http\Request;
use Rkesa\Calendar\Models\Event;
use Rkesa\Calendar\Models\EventType;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactEmail;
use Rkesa\Client\Models\ClientContactPhone;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Client\Models\ClientReferrer;
use Rkesa\Service\Models\Service;
use Rkesa\Service\Models\ServiceState;
use App\Call;
use App\Chat;
use App\ChatMember;
use App\Http\Controllers\Zadarma\History as ZadarmaHistory;
use App\Http\Controllers\Zadarma\Api as ZadarmaApi;
use App\Http\Controllers\Zadarma\Settings as ZadarmaSettings;

use Exception;
use Log;
use Auth;
use DB;

class ContactController extends Controller
{
    use MailTrait, ClientAndContactWithTrait;

    public function index(Request $request)
    {
        $user = Auth::user();

        $referrer_id = intval($request->input('referrer_id', '0'));
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == '') {
            $sort = 'created_at';
        }
        $order = $request->input('order', 'asc');
        if ($order == '') {
            $order = 'asc';
        }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $client_id = intval($request->input('client_id', '0'));
        $responsible_user_id = intval($request->input('responsible_user_id', '0'));

        $res = (object)array();
        $res->errcode = 0;
        try {
            $contacts = ClientContact::select($fields_array)->with('client', 'services', 'services.estimates', 'client_referrer', 'client_contact_phones', 'client_contact_emails');
            switch ($user->cando('clients', 'read')) {
                case 0:
                    $contacts->where('id', null);
                    break;
                case 1:
                    $event_clients = Event::where('user_id', $user->id)->pluck('client_contact_id')->all();
                    $services_clients = Service::where('responsible_user_id', $user->id)->pluck('client_contact_id')->all();
                    $clients_ids = array_unique(array_merge($event_clients, $services_clients, [1, 2]));
                    $contacts->whereIn('id', $clients_ids);
                    break;
                case 2:
                    $event_clients = Event::whereIn('user_id', $user->groupmates_ids())->pluck('client_contact_id')->all();
                    $services_clients = Service::whereIn('responsible_user_id', $user->groupmates_ids())->pluck('client_contact_id')->all();
                    $clients_ids = array_unique(array_merge($event_clients, $services_clients, [1, 2]));
                    $contacts->whereIn('id', $clients_ids);
                    break;
                case 3:
                    break;
            }
            if ($referrer_id != 0) {
                $contacts->where('client_referrer_id', $referrer_id);
            }
            if ($client_id != 0) {
                $contacts->where('client_id', $client_id);
            }
            if ($responsible_user_id != 0) {
                $contacts->where('responsible_user_id', $responsible_user_id);
            }
            $query = $request->input('query', '');
            if ($query != '') {
                // parentheses in condition
                $contacts->where(function ($c) use ($query) {
                    $c->where(DB::raw("CONCAT(`name`, ' ', `surname`)"), 'like', '%' . $query . '%')
                        ->orWhere('name', 'like', '%' . $query . '%')
                        ->orWhere('surname', 'like', '%' . $query . '%')
                        ->orWhere('nif', 'like', '%' . $query . '%')
                        ->orWhereHas('client_contact_phones', function ($q) use ($query) {
                            $q->where('phone_number', 'like', '%' . $query . '%');
                        })
                        ->orWhereHas('client_contact_emails', function ($q) use ($query) {
                            $q->where('email', 'like', '%' . $query . '%');
                        })
                        ->orWhereHas('services', function ($q) use ($query) {
                            $q->where('estimate_number', 'like', '%' . $query . '%');
                        })
                        ->orWhereHas('services.service_state', function ($q) use ($query) {
                            $q->where('name', 'like', '%' . $query . '%');
                        });
                });
            }

            $contacts->orderBy($sort, $order);

            $res->total = $contacts->count();
            $res->rows = $contacts->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();
            $contact = ClientContact::create($request->all());
            $contact->is_main_contact = ClientContact::where('client_id', $request['client_id'])->count() == 1;
            $contact->a_attributes = self::format_attributes($request['attributes_calculated']);
            $contact->save();
            if ($user->id != $contact->responsible_user_id) {
                $contact->responsible_user->notify(new ContactAssigned($contact, $user));
            }
            if ($request->filled('client_contact_phones')) {
                foreach ($request['client_contact_phones'] as $req_phone) {
                    if (trim($req_phone['phone_number']) != '') {
                        $phone = new ClientContactPhone();
                        $phone->phone_number = $req_phone['phone_number'];
                        $phone->client_contact_id = $contact->id;
                        $phone->save();
                    }
                }
            }
            if ($request->filled('client_contact_emails')) {
                foreach ($request['client_contact_emails'] as $req_email) {
                    if (trim($req_email['email']) != '') {
                        $email = new ClientContactEmail();
                        $email->email = $req_email['email'];
                        $email->client_contact_id = $contact->id;
                        $email->save();
                    }
                }
            }
            $res->contact_id = $contact->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $contact = ClientContact::with(self::$contact_with)->findOrFail($id);

        if (!$user->can_with_contact('read', $contact)) { // test contact permissions inside can_with_contact function
            return response('', 403);
        }

        if ($contact->client_id) {
            $client = Client::with(self::$client_with)->find($contact->client_id);
        } else {
            $client = null;
        }

        $contact->show_notification = false;
        $contact->show_fb_notification = false;
        $contact->save();

        // $zApi = new ZadarmaApi;
        // $zSettings = $zApi->getSettings();

        // if($zSettings->zadarma_enabled == true) {
        //     // we want to check call records
        //     // if some of those expired, should back to be updated again
        //     foreach($contact->client_history as $history) {
        //         if ($history->call) {
        //             $isAnswered = $history->call->disposition == 'answered';

        //             if($isAnswered) {
        //                 $hc = $history->call;
        //                 if($hc->is_recorded) {
        //                     if(ZadarmaHistory::isNeedToUpdateCallRecord($hc->record_link_lifetime_till)) {
        //                         $link = $zApi->getCallRecordLink($hc->call_id_with_rec);
        //                         if($link != false)
        //                             ZadarmaHistory::updateCallRecord($hc->id, $link->link, $link->lifetime_till);
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }



        return $contact;

        //        return view('client::contact/show', [
        //
        //            'client' => json_encode($client),
        //            'contact' => $contact->toJson(),
        //            'selected_service' => json_encode($service_id)
        //        ]);
    }

    //    public function edit(Request $request, $id)
    //    {
    //        $user = Auth::user();
    //        $contact = ClientContact::with(self::$contact_with)->find($id);
    //        if ($contact->client_referrer_id == null) {
    //            $contact->client_referrer_id = ClientReferrer::first()->id; // dont save
    //        }
    //
    //        if (!$user->can_with_contact('update', $contact)){
    //            return response('', 403);
    //        }
    //
    //        if ($contact->client_id) {
    //            $client = Client::with(self::$client_with)->find($contact->client_id);
    //        }else{
    //            $client = null;
    //        }
    //
    ////        $contact = ClientContact::with(array('client.services', 'client_contact_phones', 'client.events', 'client.client_contacts'))->where(array('client_id' => $client_id, 'is_main_contact' => 1 ));
    //
    //        return view('client::contact/edit',[
    ////            'contact' => $contact->first()->toJson(),
    //            'contact' => $contact->toJson(),
    //            'referrers' => ClientReferrer::all()->keyBy('id')->toJson(),
    //            'client' => json_encode($client)
    //        ]);
    //    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $contact = ClientContact::find($id);
        $old_responsible = $contact->responsible_user_id;
        if (!$user->can_with_contact('update', $contact)) {
            return response('', 403);
        }

        $res = (object)array();
        $res->errcode = 0;
        try {
            $was_main = $contact->is_main_contact;
            $old_client_id = $contact->client_id;
            $contact->fill($request->all());
            $contact->a_attributes = self::format_attributes($request['attributes_calculated']);
            $contact->is_main_contact = ClientContact::where('client_id', $request['client_id'])->count() == 0;
            $contact->save();
            if ($user->id != $contact->responsible_user_id && $old_responsible != $contact->responsible_user_id) {
                $contact->responsible_user->notify(new ContactAssigned($contact, $user));
            }
            if ($was_main) {
                $new_main_contact = ClientContact::where('client_id', $old_client_id)->first();
                $new_main_contact->is_main_contact = true;
                $new_main_contact->save();
            }
            $contact->client_contact_phones()->delete();
            if ($request->filled('client_contact_phones')) {
                foreach ($request['client_contact_phones'] as $req_phone) {
                    if (trim($req_phone['phone_number']) != '') {
                        $phone = new ClientContactPhone();
                        $phone->phone_number = $req_phone['phone_number'];
                        $phone->client_contact_id = $contact->id;
                        $phone->save();
                    }
                }
            }
            $contact->client_contact_emails()->delete();
            if ($request->filled('client_contact_emails')) {
                foreach ($request['client_contact_emails'] as $req_email) {
                    if (trim($req_email['email']) != '') {
                        $email = new ClientContactEmail();
                        $email->email = $req_email['email'];
                        $email->client_contact_id = $contact->id;
                        $email->save();
                    }
                }
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $contact = ClientContact::find($id);
        if (!$user->can_with_contact('delete', $contact) || $id == 1 || $id == 2) {  // id == 1 || 2 if it is test contacts
            return response('', 403);
        }

        ClientContact::destroy($id);
        ClientContactPhone::where('client_contact_id', $id)->delete();
        ClientContactEmail::where('client_contact_id', $id)->delete();


        $new_main_contact = ClientContact::where('client_id', $contact->client_id)->first();
        if ($new_main_contact) {
            $new_main_contact->is_main_contact = true;
            $new_main_contact->save();
        }

        // Tasks
        $events_ids = Event::where('client_contact_id', $id)->pluck('id')->toArray();
        foreach ($events_ids as $event_id) {
            app('Rkesa\Calendar\Http\Controllers\CalendarController')->destroy($request, $event_id);
        }
        // History
        ClientHistory::where('client_contact_id', $id)->delete();
        // Services
        $service_ids = Service::where('client_contact_id', $id)->pluck('id')->toArray();
        foreach ($service_ids as $service_id) {
            app('Rkesa\Service\Http\Controllers\ServiceController')->destroy($request, $service_id);
        }
    }

    public function setMainContact(Request $request, $id)
    {
        $user = Auth::user();
        $contact = ClientContact::find($id);
        if (!$user->can_with_contact('update', $contact)) {
            return response('', 403);
        }

        ClientContact::where('client_id', '=', $contact->client->id)->update(array('is_main_contact' => 0));
        ClientContact::where('id', '=', $id)->update(array('is_main_contact' => 1));
    }

    public function save_note(Request $request, $id)
    {
        $user = Auth::user();
        $contact = ClientContact::find($id);
        if (!$user->can_with_contact('update', $contact)) {
            return response('', 403);
        }

        $res = (object)array();
        $res->errcode = 0;
        try {
            $contact->note = $request['note'];
            $contact->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function fb_message(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $message = $request->input('message', '');
            if ($message == '') {
                $res->errcode = 1;
                $res->errmess = "Message can't be empty";
            } else {
                $user = Auth::user();
                $contact = ClientContact::find($id);
                if (FacebookController::fb_send($contact->fb_psid, $contact->fb_page_id, $message)) {
                    $hist = new ClientHistory;
                    $hist->message = MailTrait::format_email($message);
                    $hist->client_contact_id = $id;
                    $hist->user_id = $user->id;
                    $hist->type_id = 20;
                    $hist->save();
                }
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    /**
     * Make a call using Zadarma API .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function zadarma_request_callback(Request $request)
    {

        $user =  Auth::user();
        $post = $request->all();

        $res = (object)array();
        $res->errcode = 0;
        try {
            $api = new ZadarmaApi();
            $destination_number = (int) $post['phone_number'];
            $api->make_call($user->zadarma_internal_phonecode, $destination_number);
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }
}
