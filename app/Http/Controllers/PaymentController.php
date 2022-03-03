<?php

namespace App\Http\Controllers;

use App\UserPayment;
use YandexCheckout\Client;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        return UserPayment::select($fields_array)->get();
    }

    public function paging(Request $request)
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'created_at');
        if ($sort == ''){ $sort = 'call_start'; }
        $order = $request->input('order', 'desc');
        if ($order == ''){ $order = 'desc'; }

        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);

        $res = (object)array();
        $res->errcode = 0;
        try{
            $payments = UserPayment::select($fields_array);

            $payments->orderBy($sort, $order);

            $res->total = $payments->count();
            $res->rows = $payments->offset($offset)->limit($limit)->get();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function download_invoice(Request $request)
    {
        return response()->download(public_path("invoices/".$request->input('filename')));
    }
}
