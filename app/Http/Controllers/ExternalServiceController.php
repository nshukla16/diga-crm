<?php

namespace App\Http\Controllers;

use App\Connection;
use App\ContractorServicePayStage;
use App\GlobalSettings;
use App\Group;
use DB;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Rkesa\Service\Models\Service;
use App\Http\Controllers\Controller;
use App\Notifications\ContractorServiceChanged;
use App\Notifications\ServiceAssigned;
use App\User;
use Illuminate\Support\Facades\Log;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientContact;
use Rkesa\Client\Models\ClientContactEmail;
use Rkesa\Client\Models\ClientContactPhone;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Estimate\Models\Estimate;
use Rkesa\Estimate\Models\EstimateGroup;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateLineCategory;
use Rkesa\Estimate\Models\EstimateLineData;
use Rkesa\Estimate\Models\EstimateLineFicha;
use Rkesa\Estimate\Models\EstimateLineSeparator;
use Rkesa\Estimate\Models\EstimateUnit;
use Rkesa\Service\Models\ServicePriority;
use Rkesa\Service\Models\ServiceState;

class ExternalServiceController extends Controller
{
    public static function change_service_status_from_general_contractor(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $connection = Connection::where('url', $request['source_url'])->where('is_my', false)->firstOrFail();
            $service = Service::where('source_id', $request['source_id'])->where('connection_id', $connection->id)->orderBy('id', 'DESC')->firstOrFail();
            $service->contractor_status = $request['status'];
            $service->contractor_decline_reason = $request['contractor_decline_reason'];
            $service->save();

            if ($service->responsible_user_id != null && $service->responsible_user_id != 0) {
                $service->responsible_user->notify(new ContractorServiceChanged($service, 'general_contractor'));
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public static function change_service_status_from_contractor(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $connection = Connection::where('url', $request['source_url'])->where('is_my', true)->firstOrFail();
            $client = Client::where('connection_id', $connection->id)->firstOrFail();
            $group = Group::where('client_id', $client->id)->firstOrFail();

            $estimate_group = EstimateGroup::where('service_id', $request['source_id'])->where('group_id', $group->id)->firstOrFail();
            $estimate_group->contractor_status = $request['status'];
            $estimate_group->save();

            $service = Service::find($request['source_id']);
            if ($service->responsible_user_id != null && $service->responsible_user_id != 0) {
                $service->responsible_user->notify(new ContractorServiceChanged($service, 'contractor'));
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public static function store(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {

            $connection = Connection::where('url', $request['source_url'])->where('is_my', false)->firstOrFail();
            $global_settings = GlobalSettings::first();

            $s = json_decode($request['service']);
            $eg = json_decode($request['estimate_group']);
            $es = json_decode($request['estimate']);
            $cc = json_decode($request['client_contact']);

            $client_contact = ClientContact::where('connection_id', $connection->id)->where('source_id', $cc->id)->first();
            if ($client_contact == null) {
                $client_contact = new ClientContact;
                $client_contact->name = $cc->name;
                $client_contact->surname = $cc->surname;
                $client_contact->sex = $cc->sex;
                $client_contact->profession = $cc->profession;
                $client_contact->responsible_user_id = $connection->responsible_id;
                $client_contact->connection_id = $connection->id;
                $client_contact->source_id = $cc->id;
                $client_contact->save();

                if ($cc->client_contact_phones) {
                    foreach ($cc->client_contact_phones as $ph) {
                        $client_contact_phone = new ClientContactPhone;
                        $client_contact_phone->phone_number = $ph->phone_number;
                        $client_contact_phone->client_contact_id = $client_contact->id;
                        $client_contact_phone->save();
                    }
                    foreach ($cc->client_contact_emails as $em) {
                        $client_contact_email = new ClientContactEmail;
                        $client_contact_email->email = $em->email;
                        $client_contact_email->client_contact_id = $client_contact->id;
                        $client_contact_email->save();
                    }
                }
            }

            $service = new Service;
            $service->client_contact_id = $client_contact->id;
            if ($global_settings->contractor_service_state_id > 0) {
                $service->service_state_id = $global_settings->contractor_service_state_id;
            } else {
                $service->service_state_id = ServiceState::first()->id;
            }
            $service->responsible_user_id = $connection->responsible_id;
            $service->service_priority_id = ServicePriority::first()->id;
            $service->address = $s->address;
            $service->name = $s->name;
            $service->source_id = $s->id;
            $service->work_start = $eg->work_start;
            $service->work_end = $eg->work_end;
            $service->contractor_status = $eg->contractor_status;
            $service->contractor_file = $request['source_url'] . $eg->contractor_file;
            $service->contractor_file_name = $eg->contractor_file_name;
            $service->connection_id = $connection->id;
            $service->estimate_summ = $s->estimate_summ;
            $service->generate_estimate_number();
            $service->save();

            if ($es) {
                $eus = $es->estimate_units;

                $estimate = new Estimate;
                $estimate->subject = $es->subject;
                $estimate->deadline = 0;
                $estimate->additional_price = 0;
                $estimate->discount = 0;
                $estimate->user_id = empty($connection->responsible_id) ? User::first()->id : $connection->responsible_id;
                $estimate->blocked = false;
                $estimate->service_id = $service->id;
                $estimate->vat_type = 4;
                $estimate->save();
                $assosiation_array = array();
                foreach ($es->lines_raw as $l) {
                    $line = new EstimateLine();
                    $line->lineable_type = $l->lineable_type;
                    $line->order = $l->order;
                    $line->estimate_id = $estimate->id;
                    switch ($l->lineable_type) {
                        case '\App\EstimateLineCategory':
                            $category = new EstimateLineCategory();
                            $category->name = $l->category_name;
                            $category->is_pattern = false;
                            $category->save();
                            $line->lineable_id = $category->id;
                            break;
                        case '\App\EstimateLineSeparator':
                            $separator = new EstimateLineSeparator();
                            $separator->name = $l->separator_name;
                            $separator->is_pattern = false;
                            $separator->save();
                            $line->lineable_id = $separator->id;
                            break;
                        case '\App\EstimateLineData':
                            $data = new EstimateLineData();
                            $data->description = $l->data_description;
                            $data->note = $l->data_note;
                            $data->ppu = 0; //$l->data_ppu;
                            $data->quantity = $l->data_quantity;
                            $data->price = 0; //$l->data_price;
                            foreach ($eus as $eu) {
                                if ($eu->id == $l->data_measure) {
                                    $estimate_unit = EstimateUnit::firstOrCreate(['measure' => $eu->measure]);
                                    $data->estimate_unit_id = $estimate_unit->id;
                                }
                            }
                            $data->save();
                            $line->lineable_id = $data->id;
                            break;
                        case '\App\EstimateLineFicha':
                            $ficha = new EstimateLineFicha();
                            $ficha->description = $l->ficha_description;
                            $ficha->note = $l->ficha_note;
                            $ficha->ppu = 0; //$l->ficha_ppu;
                            $ficha->quantity = $l->ficha_quantity;
                            $ficha->price = 0; //$l->ficha_price;
                            foreach ($eus as $eu) {
                                if ($eu->id == $l->ficha_measure) {
                                    $estimate_unit = EstimateUnit::firstOrCreate(['measure' => $eu->measure]);
                                    $ficha->estimate_unit_id = $estimate_unit->id;
                                }
                            }
                            $ficha->save();
                            $line->lineable_id = $ficha->id;
                            break;
                    }
                    if (isset($l->parent_id)) {
                        $line->parent_id = $assosiation_array[$l->parent_id];
                    }
                    // correct lineable part start
                    $line->correct_lineable_id = $line->lineable_id;
                    switch ($line->lineable_type) {
                        case '\App\EstimateLineCategory':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineCategory';
                            break;
                        case '\App\EstimateLineData':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineData';
                            break;
                        case '\App\EstimateLineFicha':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineFicha';
                            break;
                        case '\App\EstimateLineSeparator':
                            $line->correct_lineable_type = '\Rkesa\Estimate\Models\EstimateLineSeparator';
                            break;
                    }
                    // end
                    $line->save();
                    $assosiation_array[$l->id] = $line->id;
                }

                $service->generate_estimate_number();
                $service->save();
            }

            if ($eg->estimate_group_pay_stages) {
                foreach ($eg->estimate_group_pay_stages as $ps) {
                    $service_pay_stage = new ContractorServicePayStage();
                    $service_pay_stage->text = $ps->text;
                    $service_pay_stage->payment_date = $ps->payment_date;
                    $service_pay_stage->percent = $ps->percent;
                    $service_pay_stage->source_id = $ps->id;
                    $service_pay_stage->connection_id = $connection->id;
                    $service_pay_stage->service_id = $service->id;
                    $service_pay_stage->save();
                }
            }

            if ($service->responsible_user_id != null && $service->responsible_user_id != 0) {
                $user = User::first();
                $service->responsible_user->notify(new ServiceAssigned($service, $user));
            }

            $hist = new ClientHistory;
            $hist->type_id = 1;
            $hist->message = trans('calendar.Service') . ': ' . $service->get_service_number() . ' - ' . $service->name . ' ' . trans('template.received_from_general_contractor');
            $hist->client_contact_id = $client_contact->id;
            $hist->save();

            $res->service_id = $service->id;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
