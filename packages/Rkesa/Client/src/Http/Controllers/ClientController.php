<?php

namespace Rkesa\Client\Http\Controllers;

use App\Events\IncomingClient;
use App\Http\Controllers\Controller;
use App\Http\Traits\ClientAndContactWithTrait;
use App\Http\Traits\MailTrait;
use App\Site;
use Carbon\Carbon;
use Rkesa\Client\Models\ClientCalculation;
use UrlSigner;
use App\User as User;
use Illuminate\Http\Request;
use Rkesa\Calendar\Models\EventType;
use Rkesa\CalendarExtended\Models\EventGroup;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactEmail;
use Rkesa\Client\Models\ClientContactPhone;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Client\Models\ClientReferrer;
use Rkesa\Estimate\Http\Controllers\EstimateController;
use Rkesa\Client\Http\Helpers\CompanyPDFCreator;
use Rkesa\Service\Models\Service;
use Rkesa\Service\Models\ServiceAttachment;
use Rkesa\Service\Models\ServiceState;
use Rkesa\Service\Models\Aru;
use Rkesa\Calendar\Models\Event;
use Rkesa\Estimate\Models\Estimate;
use App\GlobalSettings;
use App\Group;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use DateTime;
use DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    use MailTrait, ClientAndContactWithTrait;

    public function index(Request $request)
    {
        $user = Auth::user();

        $contractor = $request->input('contractor', false);
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
        $referrer_id = intval($request->input('referrer_id', '0'));

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try {
            $clients = Client::select($fields_array)->with('client_contacts.services', 'client_contacts.services.estimates', 'client_referrer', 'client_contacts.client_contact_phones', 'calculations');
            switch ($user->cando('clients', 'read')) {
                case 0:
                    $clients->where('id', null);
                    break;
                case 1:
                    $event_clients = Event::where('user_id', $user->id)->pluck('client_contact_id')->all();
                    $services_clients = Service::where('responsible_user_id', $user->id)->pluck('client_contact_id')->all();
                    $contact_ids = array_unique(array_merge($event_clients, $services_clients, [1, 2]));
                    $client_ids = ClientContact::whereIn('id', $contact_ids)->pluck('client_id')->all();
                    $clients->whereIn('id', $client_ids);
                    break;
                case 2:
                    $event_clients = Event::whereIn('user_id', $user->groupmates_ids())->pluck('client_contact_id')->all();
                    $services_clients = Service::whereIn('responsible_user_id', $user->groupmates_ids())->pluck('client_contact_id')->all();
                    $contact_ids = array_unique(array_merge($event_clients, $services_clients, [1, 2]));
                    $client_ids = ClientContact::whereIn('id', $contact_ids)->pluck('client_id')->all();
                    $clients->whereIn('id', $client_ids);
                    break;
                case 3:
                    break;
            }
            if ($referrer_id != 0) {
                $clients->where('client_referrer_id', $referrer_id);
            }
            if ($contractor == 'true') {
                $clients->where('is_group', true);
            }
            $query = $request->input('query', '');
            if ($query != '') {
                // parentheses in condition
                $clients->where(function ($c) use ($query) {
                    $c->where('name', 'like', '%' . $query . '%')
                        ->orWhere('email', 'like', '%' . $query . '%')
                        ->orWhere('nif', 'like', '%' . $query . '%')
                        ->orWhere('id', 'like', '%' . $query . '%')
                        ->orWhere('phone', 'like', '%' . $query . '%')
                        ->orWhere('client_group', 'like', '%' . $query . '%');
                });
            }

            $clients->orderBy($sort, $order);

            $res->total = $clients->count();
            $res->rows = $clients->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    //    public function create()
    //    {
    //        $client = new Client;
    //        $client->client_referrer_id  =  ClientReferrer::first()->id;
    //        $client->a_attributes = [];
    //
    //        return view('client::client/create',[
    //            'referrers' => ClientReferrer::all()->keyBy('id')->toJson(),
    //            'client' => json_encode($client)
    //        ]);
    //    }

    public function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $client = Client::create($request->all());
            $client->creator_user_id = Auth::user()->id;
            $client->a_attributes = self::format_attributes($request['attributes_calculated']);
            $client->save();

            if ($client->is_group == true) {
                $group = Group::find($request['group_id']);
                if ($group != null) {
                    $group->client_id = $client->id;
                    $group->save();
                }
            }

            $res->id = $client->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function get_company_pdf_link(Request $request, $id)
    {
        $user = Auth::user();
        $client = Client::findOrFail($id);
        if (!$user->can_with_client('read', $client)) {
            return response('forbidden', 403);
        }
        return response()->json(['link' => UrlSigner::sign(env('APP_URL') . '/api/companies/pdf/' . $id, Carbon::now()->addHours(9))]);
    }

    public function show_company(Request $request, $id)
    {
        if (UrlSigner::validate(url()->full())) {
            $client = Client::findOrFail($id);
            $creator = new CompanyPDFCreator;
            $format = $request->input('format', 'pdf');
            switch ($format) {
                case 'html':
                    $result = $creator->render_html($client);
                    return Response($result);
                    break;
                case 'pdf':
                    $result =  $creator->render_pdf($client);
                    $headers = [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $client->name . '.pdf"',
                        'Accept-Ranges' => 'bytes',
                        'Content-Length' => strlen($result)
                    ];
                    return Response($result, 200, $headers);
                    break;
            }
        } else {
            return response('forbidden', 403);
        }
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();

        $client = Client::with(self::$client_with)->findOrFail($id);

        if (!$user->can_with_client('read', $client)) { // test company permissions inside can_with_client function
            return response('', 403);
        }

        $no_tasks = false;
        $gs = GlobalSettings::first();
        if ($gs->check_clients_no_tasks) {
            if ($request->session()->has('no_tasks') && $request->session()->get('no_tasks')) {
                $request->session()->forget('no_tasks');
                $no_tasks = true;
            }

            if ($client->redirect_to_this_client() && !$client->ignore_doesnt_have_tasks) {
                $no_tasks = true;
            }
        }

        return $client->load('calculations');

        //        return view('client::client/show', [
        //            'users' => User::all()->keyBy('id')->toJson(),
        //            'client' => $client,
        //            'event_groups' => config('modules_enabled')['calendar_extended'] ? EventGroup::all()->toJson() : "{}",
        //            'no_tasks' => json_encode($no_tasks)
        //        ]);
    }

    //    public function edit(Request $request, $id)
    //    {
    //        $user = Auth::user();
    //        $client = Client::find($id);
    //        if (!$user->can_with_client('update', $client)){
    //            return redirect('/');
    //        }
    //
    //        if ($client->client_referrer_id == null) {
    //            $client->client_referrer_id = ClientReferrer::first()->id; // dont save
    //        }
    //
    //        return view('client::client/edit',[
    //            'client' => $client,
    //        ]);
    //    }

    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();
            $client = Client::find($id);

            if (!$user->can_with_client('update', $client)) {
                return response('', 403);
            }

            $client->fill($request->all());
            $client->a_attributes = self::format_attributes($request['attributes_calculated']);
            $client->save();
            $res->id = $client->id;
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
        $client = Client::findOrFail($id);
        if (!$user->can_with_client('delete', $client) || $id == 1) { // id == 1 if it is test company
            return response('', 403);
        }

        // Contacts and phones
        $contact_ids = ClientContact::where('client_id', $client->id)->pluck('id')->toArray();
        foreach ($contact_ids as $contact_id) {
            $e = app('Rkesa\Client\Http\Controllers\ContactController')->destroy($request, $contact_id);
        }
        $client->delete();
    }

    public function save_note(Request $request, $id)
    {
        $user = Auth::user();
        $client = Client::find($id);
        if (!$user->can_with_client('update', $client)) {
            return response('', 403);
        }

        $res = (object)array();
        $res->errcode = 0;
        try {
            $client->note = $request['note'];
            $client->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function ignore_no_tasks(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        if (!$client->redirect_to_this_client()) {
            return redirect('/');
        }

        $client->ignore_doesnt_have_tasks = true;
        $client->save();

        return redirect('/clients/' . $client->id);
    }

    public function webmail_autocomplete(Request $request)
    {
        $email = $request->input('email', '');
        $user = User::where('email', $email)->first();
        if ($user->email_suggestions) {
            $query = $request->input('q', '');
            $contacts = ClientContact::with('client_contact_emails')
                ->where('name', 'like', '%' . $query . '%')
                ->orWhere('surname', 'like', '%' . $query . '%')
                ->orWhereHas('client_contact_emails', function ($q) use ($query) {
                    $q->where('email', 'like', '%' . $query . '%');
                })
                ->limit(50)->get()->toArray();
            $contacts = array_filter($contacts, function ($contact) use ($query) {
                $trimmed = preg_replace('/\s+/', '', self::email_from_contact($contact, $query));
                return !empty(self::email_from_contact($contact, $query)) && $trimmed != '';
            });
            $contacts = array_slice($contacts, 0, 15);
            $contacts = array_map(
                function ($contact) use ($query) {
                    $res = $contact['name'] . ' ' . $contact['surname'] . ' <' . self::email_from_contact($contact, $query) . '>';
                    return str_replace(';', ',', $res);
                },
                $contacts
            );
            return response()->json($contacts);
        } else {
            return response()->json([]);
        }
    }

    private function email_from_contact($contact, $query)
    {
        if ($query != '') {
            foreach ($contact['client_contact_emails'] as $email) {
                if (strpos($email['email'], $query) !== false) {
                    return $email['email'];
                }
            }
        } else {
            return $contact['client_contact_emails'][0]['email'];
        }
    }

    public function get_new_requests(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $clients = ClientContact::where('show_notification', true)->with('client_referrer')->get()->toArray();
            $res->clients = $clients;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
    public function get_new_fb_messages(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $clients = ClientContact::where('show_fb_notification', true)->with('client_referrer')->get()->toArray();
            $res->clients = $clients;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function incoming_email(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $from = $request->input('from', '');
            $to = $request->input('to', '');
            $text = $request->input('text', '');
            $cc_emails = ClientContactEmail::where('email', 'like', '%' . $from . '%')->get();
            $user = User::where('email', $to)->first();
            if (!is_null($user)) {
                if ($cc_emails) {
                    foreach ($cc_emails as $cc_email) {
                        $contact = $cc_email->client_contact;
                        $hist = new ClientHistory;
                        $hist->message = MailTrait::format_email($text);
                        $hist->client_contact_id = $contact->id;
                        $hist->type_id = 4;
                        $hist->user_id = $user->id;
                        $hist->save();
                    }
                } else {
                    $res->errcode = 1;
                    $res->errmess = trans('client::client.Contact_with_email_not_found', [], $user->site_language);
                }
            } else {
                $res->errcode = 1;
                $res->errmess = 'User not found';
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function outcoming_email(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            # if connected few accounts
            $from = json_decode($request->input('from', '[]'));
            $to = $request->input('to', '');
            $text = $request->input('text', '');
            $cc_emails = ClientContactEmail::where('email', 'like', '%' . $to . '%')->get();
            if ($cc_emails) {
                foreach ($cc_emails as $cc_email) {
                    $contact = $cc_email->client_contact;
                    $user = null;
                    foreach ($from as $email) {
                        $result = User::where('email', $email)->first();
                        if ($result) {
                            $user = $result;
                        }
                    }
                    if (!is_null($user)) {
                        $hist = new ClientHistory;
                        $hist->message = MailTrait::format_email($text);
                        $hist->client_contact_id = $contact->id;
                        $hist->type_id = 3;
                        $hist->user_id = $user->id;
                        $hist->save();
                    } else {
                        $res->errcode = 1;
                        $res->errmess = 'Can\'t save mail to history: not authenticated';
                    }
                }
            } else {
                $res->errcode = 1;
                $res->errmess = 'Can\'t save mail to history: client not found';
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function history_message(Request $request, $client_id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $hist = new ClientHistory;
            $hist->type_id = 1; //Comment
            $hist->user_id = Auth::user()->id;
            $hist->client_contact_id = $client_id;
            $hist->message = $request['message'];
            $hist->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
