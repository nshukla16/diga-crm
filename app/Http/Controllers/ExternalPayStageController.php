<?php

namespace App\Http\Controllers;

use App\Connection;
use App\ContractorServicePayStage;
use App\Notifications\ContractorInvoiceReceived;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Rkesa\Client\Models\Client;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Estimate\Models\EstimateGroupPayStage;
use Rkesa\Service\Models\Service;

class ExternalPayStageController extends Controller
{
    public static function paid_from_general_contractor(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $connection = Connection::where('url', $request['source_url'])->where('is_my', false)->firstOrFail();
            $ps = ContractorServicePayStage::where('source_id', $request['source_id'])->where('connection_id', $connection->id)->orderBy('id', 'DESC')->firstOrFail();
            $ps->paid = true;
            $ps->fact_paid = $request['fact_paid'];
            $ps->save();

            $service = Service::find($ps->service_id);

            $hist = new ClientHistory();
            $hist->type_id = 1;
            $hist->message = trans('calendar.Service') . ': ' . $service->get_service_number() . ' - ' . $service->name . ' ' . trans('template.general_contractor_paid') . ' ' . $ps->fact_paid;
            $hist->client_contact_id = $service->client_contact_id;
            $hist->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public static function upload_invoice_status_from_contractor(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $connection = Connection::where('url', $request['source_url'])->where('is_my', true)->firstOrFail();
            $estimate_group_pay_stage = EstimateGroupPayStage::with('estimate_group')->find($request['source_id']);
            $estimate_group_pay_stage->invoice_file = $request['invoice_file'];
            $estimate_group_pay_stage->invoice_file_name = $request['invoice_file_name'];
            $estimate_group_pay_stage->save();

            $service = Service::find($estimate_group_pay_stage->estimate_group->service_id);
            if ($service->responsible_user_id != null && $service->responsible_user_id != 0) {
                $service->responsible_user->notify(new ContractorInvoiceReceived($service, $request['invoice_file'], $request['invoice_file_name']));
            }

            $hist = new ClientHistory();
            $hist->type_id = 1;
            $hist->message = trans('calendar.Service') . ': ' . $service->get_service_number() . ' - ' . $service->name . ' ' . trans('template.received_invoice_from_contractor') . ': ' . $request['invoice_file_name'];
            $hist->client_contact_id = $service->client_contact_id;
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
