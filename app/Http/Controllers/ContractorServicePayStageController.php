<?php

namespace App\Http\Controllers;

use App\Connection;
use App\ContractorServicePayStage;
use App\Http\Traits\SaasAuthTrait;
use App\Http\Traits\SaasPayStageTrait;
use App\Http\Traits\SaasServiceTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\Service;

class ContractorServicePayStageController extends Controller
{
    use SaasAuthTrait, SaasPayStageTrait;

    public function update(Request $request, $id)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $ps = ContractorServicePayStage::find($id);
            if ($ps->invoice_file == null && $request['invoice_file'] != null) {
                $service = Service::find($ps->service_id);
                $connection = Connection::find($ps->connection_id);
                $to_replace = array("http://", "https://", "www.", ".diga.pt");
                $auth = self::get_access_token();
                $token = $auth['access_token'];
                $source_url = str_replace($to_replace, "", env('APP_URL'));

                $send = self::upload_invoice_from_contractor($connection->url, $source_url, $ps->source_id, env('APP_URL') . $request['invoice_file'], $request['invoice_file_name'], $token);

                $hist = new ClientHistory();
                $hist->user_id = Auth::user()->id;
                $hist->type_id = 1;
                $hist->message = trans('calendar.Service') . ': ' . $service->get_service_number() . ' - ' . $service->name . ' - ' . trans('template.sent_invoice_to_general_contractor') . ' ' . $request['invoice_file_name'];
                $hist->client_contact_id = $service->client_contact_id;
                $hist->save();


                $ps->invoice_file = $request['invoice_file'];
                $ps->invoice_file_name = $request['invoice_file_name'];
                $ps->save();
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
