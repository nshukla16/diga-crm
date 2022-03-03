<?php

namespace Rkesa\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookController;
use App\Http\Traits\ClientAndContactWithTrait;
use App\Http\Traits\MailTrait;
use App\Notifications\ContactAssigned;
use App\User as User;
use Illuminate\Http\Request;
use Rkesa\Client\Models\ClientHistory;
use App\Call;
use App\Chat;

use App\Http\Controllers\Zadarma\History as ZadarmaHistory;
use App\Http\Controllers\Zadarma\Api as ZadarmaApi;
use App\Http\Controllers\Zadarma\Settings as ZadarmaSettings;

use Exception;
use Log;
use Auth;
use DB;
use Rkesa\Client\Models\ClientContact;

class ClientHistoryController extends Controller
{
    public function index(Request $request, $id)
    {
        $res = (object) array();
        $res->errcode = 0;

        try {
            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $sort = $request->input('sort', 'created_at');
            if ($sort == '') {
                $sort = 'created_at';
            }
            $order = $request->input('order', 'desc');
            if ($order == '') {
                $order = 'desc';
            }

            $contact_ids = ClientContact::where('client_id', $id)->pluck('id')->toArray();

            $histories = ClientHistory::with(['user', 'service', 'service_attachment', 'event.event_type', 'client_contact', 'call'])->whereIn('client_contact_id', $contact_ids);
            $histories->orderBy($sort, $order);
            $res->total = $histories->count();
            $res->rows = $histories->offset($offset)->limit($limit)->get();

            foreach ($res->rows as $history) {
                if ($history->type_id == 3 || $history->type_id == 4) {
                    $history->message = "";
                }
            }

            $zApi = new ZadarmaApi;
            $zSettings = $zApi->getSettings();

            if ($zSettings->zadarma_enabled == true) {
                foreach ($res->rows as $history) {
                    if ($history->call) {
                        $isAnswered = $history->call->disposition == 'answered';

                        if ($isAnswered) {
                            $hc = $history->call;
                            if ($hc->is_recorded) {
                                if (ZadarmaHistory::isNeedToUpdateCallRecord($hc->record_link_lifetime_till)) {
                                    $link = $zApi->getCallRecordLink($hc->call_id_with_rec);
                                    if ($link != false)
                                        ZadarmaHistory::updateCallRecord($hc->id, $link->link, $link->lifetime_till);
                                }
                            }
                        }
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
}
