<?php

namespace App\Http\Controllers;

use Log;
use App\Site;
use App\User;
use DateTime;
use Exception;
use App\AuthPhoto;
use Carbon\Carbon;
use App\GlobalSettings;
use App\CheckfrontField;
use App\EstimateGroupWorkerActivity;
use App\Events\NewFBMessage;
use Illuminate\Http\Request;
use App\Http\Traits\SMSTrait;
use App\Events\IncomingClient;
use App\Http\Traits\MailTrait;
use Rkesa\Client\Models\Client;
use function PHPSTORM_META\type;
use Rkesa\Calendar\Models\Event;
use Rkesa\Hr\Models\Timetracker;
use Rkesa\Service\Models\Service;
use App\Http\Helpers\PhotoEncoder;
use Rkesa\Estimate\Models\Estimate;
use Illuminate\Support\Facades\Auth;
use Rkesa\Service\Models\ServiceType;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Client\Models\ClientReferrer;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Client\Models\ClientContactEmail;
use Rkesa\Client\Models\ClientContactPhone;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Service\Models\ServiceAttachment;

use Rkesa\Estimate\Models\EstimateGroupWorker;
use Rkesa\Estimate\Models\EstimateLineCategory;

class ApiController extends Controller
{
    use SMSTrait, MailTrait;

    public function api(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $type = $request->input('type', '');
            switch ($type) {
                case 'new_client':
                    return self::incoming_request($request);
                case 'service_paid':
                    return self::service_paid($request);
                default:
                    throw new Exception('Invalid type');
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function service_paid(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $token = $request->input('token', '');
            $paid_summ = intval(trim($request->input('paid_summ', '')));
            $site = Site::where('token', $token)->first();
            if ($site != null) {
                $external_id = trim($request->input('external_id', ''));
                $service = Service::where('external_id', $external_id)->first();
                $service->paid_summ = $paid_summ;
                $service->save();
            } else {
                $res->errcode = 1;
                $res->errmess = 'Not valid token';
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function incoming_request(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $token = $request->input('token', '');
            $site = Site::where('token', $token)->first();
            if ($site != null) {
                $user_name = trim($request->input('name', ''));
                $user_surname = trim($request->input('surname', ''));
                $user_email = trim($request->input('email', ''));
                $user_phone = trim($request->input('phone', ''));
                $service_name = trim($request->input('service_name', ''));
                $service_address = trim($request->input('service_address', ''));
                $service_note = trim($request->input('service_note', ''));
                $service_type = trim($request->input('service_type', ''));
                $history_message = str_replace('&quot;', '"', trim($request->input('message', '')));
                $extra = json_decode($request->input('extra', '{}'), true);
                if ($extra == null) {
                    $extra = array();
                }
                $file = $request->file('file');
                if ($user_name == '' && $user_email == '' && $user_phone == '' && $history_message == '') {
                    $res->errcode = 1;
                    $res->errmess = 'Empty fields';
                } else {
                    if ((string)(int)$user_name == $user_name) { // Contains only numbers
                        $res->errcode = 1;
                        $res->errmess = 'Spam filter';
                    } else {
                        if ($service_type != '' && ServiceType::where('name', $service_type)->first() == null) {
                            $res->errcode = 1;
                            $res->errmess = 'Service type not found';
                        } else {
                            $client_referrer = ClientReferrer::firstOrCreate(['title' => $site->domain]);
                            self::new_client($client_referrer, $site->id, 5, $user_name, $user_surname, null, $user_email, $user_phone, $service_name, $service_address, false, $service_note, $service_type, $history_message, $extra, $file, '', '');
                        }
                    }
                }
            } else {
                $res->errcode = 1;
                $res->errmess = 'Not valid token';
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public static function new_client($client_referrer, $site_id, $hist_type_id, $user_name, $user_surname, $user_gender, $user_email, $user_phone, $service_name, $service_address, $service_address_autocomplete_disabled, $service_note, $service_type, $history_message, $extra, $file, $psid, $fb_page_id)
    {
        // ==============================================  CREATE CLIENT
        //        $client = new Client;
        //        $client->a_attributes = array();
        //        $client->save();

        $contact = new ClientContact;
        //        $contact->client_id = $client->id;
        $contact->client_referrer_id = $client_referrer->id;
        $contact->show_notification = true;
        $contact->name = $user_name;
        $contact->surname = $user_surname;
        $contact->sex = $user_gender;
        $contact->is_main_contact = true;
        $contact->fb_psid = $psid;
        $contact->fb_page_id = $fb_page_id;
        $contact->a_attributes = array();
        $contact->save();

        if ($user_email != null) {
            $contact_email = new ClientContactEmail;
            $contact_email->email = $user_email;
            $contact_email->client_contact_id = $contact->id;
            $contact_email->save();
        }

        if ($user_phone != null) {
            $contact_phone = new ClientContactPhone;
            $contact_phone->phone_number = $user_phone;
            $contact_phone->client_contact_id = $contact->id;
            $contact_phone->save();
        }

        // ==============================================  CREATE SERVICE
        $gs = GlobalSettings::first();

        $service = new Service;
        $service->name = $service_name;
        $service->client_contact_id = $contact->id;
        $service->generate_estimate_number();
        $service->responsible_user_id = $gs->responsible_user_id;
        $service->service_state_id = $gs->new_service_state_id;
        $service->service_priority_id = 1; // Hardcode Normal
        $service->autocomplete_disabled = $service_address_autocomplete_disabled;
        if ($service_type != '') {
            $service->service_type_id = ServiceType::where('name', $service_type)->first()->id;
        }
        $service->address = $service_address;
        $service->note = $service_note;
        $service->aru_id = null;
        if (array_key_exists('estimate_summ', $extra)) {
            $service->estimate_summ = $extra['estimate_summ'];
        }
        if (array_key_exists('paid_summ', $extra)) {
            $service->paid_summ = $extra['paid_summ'];
        }
        if (array_key_exists('external_id', $extra)) {
            $service->external_id = $extra['external_id'];
        }
        $service->save();

        // ============================================== CREATE SERVICE ATTACHMENT
        if ($file) {
            $name_orig = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
            $path = 'img/uploads/service/';
            $name = uniqid('', true) . '.' . $ext;
            $file->move($path, $name);

            $attachment = new ServiceAttachment;
            $attachment->name = $name_orig;
            $attachment->file = '/' . $path . $name;
            $attachment->service_id = $service->id;
            $attachment->save();
        }

        // ==============================================  CREATE TASK
        $event = new Event;
        $event->start = (new DateTime())->getTimestamp();
        $event->user_id = $gs->responsible_user_id;
        $event->client_contact_id = $contact->id;
        $event->event_type_id = $gs->new_event_type_id;
        $event->service_id = $service->id;
        $event->done = false;
        $event->save();

        // ==============================================  CREATE MESSAGE
        $hist = new ClientHistory;
        $hist->client_contact_id = $contact->id;
        $hist->type_id = $hist_type_id;
        $hist->site_id = $site_id;
        $hist->message = MailTrait::format_email($history_message);
        if (isset($attachment)) {
            $hist->service_attachment_id = $attachment->id;
        }
        $hist->save();

        //        $client->client_contacts;
        $contact->client_referrer;
        broadcast(new IncomingClient($contact->toArray()));

        $replace_from = array('{name}', '{email}', '{phone}', '{message}', '{referrer}');
        $replace_to = array($user_name, $user_email, $user_phone, $history_message, $client_referrer->title);

        // send sms to client
        if ($gs->incoming_sms) {
            $sms_data = array(
                'to' => $user_phone,
                'text' => str_replace($replace_from, $replace_to, $gs->incoming_sms_text)
            );
            self::send_sms($sms_data);
        }

        // send mail to admin
        if ($gs->incoming_mail) {
            $mail_data = array(
                'to' => explode(',', $gs->incoming_mail_address),
                'subject' => str_replace($replace_from, $replace_to, $gs->incoming_mail_subject),
                'body' => str_replace($replace_from, $replace_to, $gs->incoming_mail_text)
            );
            self::send_mail($mail_data);
        }

        return $contact;
    }

    public function time_point(Request $request)
    {
        $type = $request['type'];
        $user_id = Auth::user()->id;

        if ($type == 1 || $type == 3) {
            $t = Timetracker::where('user_id', $user_id)->orderBy('start', 'desc')->first();

            if (($t == null && $type == 1) || ($t->type == 1 && $type == 3 && $t->finish != null) || ($t->type == 3 && $type == 1 && $t->finish != null)) {
                $model = new Timetracker();
                $model->user_id = $user_id;
                $model->estimate_id = null;
                $model->lat = 0;
                $model->lng = 0;
                $model->start = Carbon::now();
                $model->type = $type;
                $model->save();

                if ($type == 1) {
                    $file = $request->file('image');
                    $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                    $path = 'img/uploads/timetracker/';
                    $name = uniqid('', true) . '.' . $ext;
                    $file->move($path, $name);
                    $image_path = '/' . $path . $name;

                    $model->photo = $image_path;
                    $model->save();
                }
            } else {
                return response()->json(["result" => "error", "message" => "invalid type"], 400, [], JSON_NUMERIC_CHECK);
            }
        } else {
            $t = Timetracker::where('user_id', $user_id)->orderBy('start', 'desc')->first();
            if ($t != null && (($t->type == 1 && $type == 2) || ($t->type == 3 && $type == 4))) {
                $t->finish = Carbon::now();
                $t->save();
            } else {
                return response()->json(["result" => "error", "message" => "invalid type"], 400, [], JSON_NUMERIC_CHECK);
            }
        }

        return response()->json(["result" => "ok", "message" => "ok"], 200, [], JSON_NUMERIC_CHECK);
    }

    public function login_with_photo(Request $request)
    {
        $file = $request->file('image');
        $name_orig = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $path = 'img/uploads/auth/';
        $name = uniqid('', true) . '.' . $ext;
        $file->move($path, $name);

        $knowns = [];
        $known_files = [];
        $users = User::whereNotNull('photo_encodings')->get();
        $c = 0;
        foreach ($users as $user) {
            $c++;
            $knowns[] = $user->id;
            $known_files[] = [
                'name' => 'known[' . ($c - 1) . ']',
                'contents' => $user->photo_encodings,
            ];
        }

        $guzzle = new \GuzzleHttp\Client;

        $k = array_merge([
            [
                'name'     => 'knowns',
                'contents' => json_encode($knowns)
            ],
            [
                'name'     => 'unknown',
                'contents' => file_get_contents(public_path($path . $name)),
                'filename' => $name
            ],
        ], $known_files);

        $response = $guzzle->request('POST', env('ERP_FACE_RECOGNITION_URL', '127.0.0.1:8888') . '/recognize', [
            'multipart' => $k
        ]);

        $response_string = (string) $response->getBody();

        $auth_photo = new AuthPhoto;
        $auth_photo->name = $name_orig;
        $auth_photo->url = '/' . $path . $name;
        $auth_photo->result = $response_string;
        $auth_photo->save();

        $response_decoded = json_decode($response_string, true);

        if ($response_decoded['result'] == 'ok') {
            $user = User::with('roles')->find($response_decoded['id']);

            $access_token = $this->getBearerTokenByUser($user, 1, false);

            return response()->json(["user" => $user, "access_token" => $access_token], 200, [], JSON_NUMERIC_CHECK);
        } else {
            return Response($response_string, 401);
        }
    }

    public function checkfront_booking_status_webhook(Request $request)
    {
        $booking = $request['booking'];
        Log::info($booking);
        $service = Service::where('external_id', $booking['@attributes']['booking_id'])->first();
        if ($service) {
            switch ($booking['status']) {
                case 'PART':
                case 'PAID':
                    $service->estimate_summ = $booking['order']['total'];
                    $service->paid_summ = $booking['order']['paid_total'];
                    $service->save();
                    break;
                case 'PEND':
                case 'HOLD':
                case 'WAIT':
                case 'STOP':
                case 'VOID':
                    break;
            }
        } else {
            $client_referrer = ClientReferrer::firstOrCreate(['title' => 'Checkfront']);

            $user_name = $booking['meta']['customer_first_name'];
            $user_surname = self::to_string_with_empty_array($booking['meta']['customer_last_name']);
            $user_email = self::to_string_with_empty_array($booking['customer']['email']);
            $user_phone = self::to_string_with_empty_array($booking['customer']['phone']);

            $extra['estimate_summ'] = $booking['order']['total'];
            $extra['paid_summ'] = $booking['order']['paid_total'];
            $extra['external_id'] = $booking['@attributes']['booking_id'];
            $history_message = '';
            $file = null;

            $service_name = '';
            if (array_key_exists('sku', $booking['order']['items']['item'])) {
                $b_item = $booking['order']['items']['item'];
                $item_id = $b_item['@attributes']['item_id'];
                $item = CheckfrontController::get_item_by_id($item_id);
                $service_name = $item['name'];
            } else {
                $b_item = $booking['order']['items']['item'][0];
                $item_id = $b_item['@attributes']['item_id'];
                $item = CheckfrontController::get_item_by_id($item_id);
                $service_name = $item['name'];
            }

            $epoch = $booking['start_date'];
            $datetime_string = (new DateTime("@$epoch"))->format('d.m.Y');

            $service_note = 'Quantity: ' . $b_item['qty'] . ' Date: ' . $datetime_string;

            $service_type = '';
            $service_address = '';

            $checkfront_fields = CheckfrontField::orderBy('order')->get();
            foreach ($checkfront_fields as $checkfront_field) {
                foreach ($booking['fields'] as $field_key => $field_value) {
                    if ($field_key == $checkfront_field->field_name) {
                        $v = $checkfront_field->note . self::to_string_with_empty_array($booking['fields'][$checkfront_field->field_name]);
                        switch ($checkfront_field->destination) {
                            case 1: // service_note
                                if ($checkfront_field->type == 1) { // overwrite
                                    $service_note = $v;
                                } else { // append
                                    $service_note .= ($service_note == '' ? '' : ' ') . $v;
                                }
                                break;
                            case 2: // service_address
                                if ($checkfront_field->type == 1) { // overwrite
                                    $service_address = $v;
                                } else { // append
                                    $service_address .= ($service_address == '' ? '' : ' ') . $v;
                                }
                                break;
                        }
                    }
                }
            }

            $contact = self::new_client($client_referrer, null, 6, $user_name, $user_surname, null, $user_email, $user_phone, $service_name, $service_address, true, $service_note, $service_type, $history_message, $extra, $file, '', '');

            $estimate = new Estimate;
            $estimate->service_id = Service::where('client_contact_id', $contact->id)->first()->id;
            $estimate->vat_type = 2; // Other
            $estimate->price = $booking['order']['total'];
            $estimate->save();


            // if single item
            if (array_key_exists('sku', $booking['order']['items']['item'])) {
                $booking_item = $booking['order']['items']['item'];

                $item_id = $booking['order']['items']['item']['@attributes']['item_id'];
                $item = CheckfrontController::get_item_by_id($item_id);

                $line = self::createCheckfrontCategory($estimate->id);
                $line2 = self::createCheckfrontData(
                    $estimate->id,
                    1,
                    $item['name'],
                    floatval($booking_item['total']) / floatval($booking_item['qty']),
                    floatval($booking_item['qty']),
                    floatval($booking_item['total']),
                    $line->id
                );

                //                Log::info($item);
            } else {
                $parent_id = 0;
                foreach ($booking['order']['items']['item'] as $i => $booking_item) {
                    $item_id = $booking_item['@attributes']['item_id'];
                    $item = CheckfrontController::get_item_by_id($item_id);
                    if ($i == 0) {
                        $line = self::createCheckfrontCategory($estimate->id);
                        $parent_id = $line->id;

                        $line2 = self::createCheckfrontData(
                            $estimate->id,
                            2,
                            $item['name'],
                            floatval($booking_item['total']) / floatval($booking_item['qty']),
                            floatval($booking_item['qty']),
                            floatval($booking_item['total']),
                            $line->id
                        );
                    } else {
                        $line2 = self::createCheckfrontData(
                            $estimate->id,
                            $i + 2,
                            $item['name'],
                            floatval($booking_item['total']) / floatval($booking_item['qty']),
                            floatval($booking_item['qty']),
                            floatval($booking_item['total']),
                            $parent_id
                        );
                    }

                    //                    Log::info($item);
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function createCheckfrontData($estimate_id, $order, $name, $ppu, $quantity, $price, $parent_id)
    {
        // DONT FORGET TO FILL correct_lineable!!!!!!!!!!! (see EstimateController@update:530)
        $line = new EstimateLine();
        $line->lineable_type = '\App\EstimateLineData';
        $line->order = $order;
        $line->estimate_id = $estimate_id;

        $data = new EstimateLineData();
        $data->description = $name;
        $data->note = '';
        $data->ppu = $ppu;
        $data->quantity = $quantity;
        $data->price = $price;

        $estimate_unit = self::findUnitOrCreate();

        $data->estimate_unit_id = $estimate_unit->id;
        $data->save();
        $line->lineable_id = $data->id;
        $line->parent_id = $parent_id;
        $line->save();

        return $line;
    }

    private function createCheckfrontCategory($estimate_id)
    {
        $line = new EstimateLine();
        $line->lineable_type = '\App\EstimateLineCategory';
        $line->order = 1;
        $line->estimate_id = $estimate_id;

        $category = new EstimateLineCategory();
        $category->name = 'CHECKFRONT';
        $category->is_pattern = false;
        $category->save();
        $line->lineable_id = $category->id;
        $line->save();
        return $line;
    }

    private function to_string_with_empty_array($string_or_array)
    {
        if (is_array($string_or_array)) {
            return '';
        } else {
            return $string_or_array;
        }
    }

    private function findUnitOrCreate()
    {
        $gs = GlobalSettings::first();
        $estimate_unit = null;
        switch ($gs->default_language) {
            case 'ru':
                $estimate_unit = EstimateUnit::firstOrCreate(['measure' => 'шт']);
                break;
            case 'pt':
                $estimate_unit = EstimateUnit::firstOrCreate(['measure' => 'un']);
                break;
            case 'en':
                $estimate_unit = EstimateUnit::firstOrCreate(['measure' => 'un']);
                break;
        }
        return $estimate_unit;
    }

    public function auth0_users()
    {
        $res = [];

        $users = User::get();
        foreach ($users as $user) {
            $item = [
                'email' => $user->email,
                'name' => $user->name,
                'blocked' => !$user->active,
                'custom_password_hash' => [
                    'algorithm' => 'bcrypt',
                    'hash' => [
                        'value' => $user->password
                    ]
                ]
            ];

            array_push($res, $item);
        }

        return response()->json($res);
    }
}
