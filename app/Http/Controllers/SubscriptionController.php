<?php

namespace App\Http\Controllers;

use stdClass;
use Exception;
use App\Module;
use App\Setting;
use Carbon\Carbon;
use App\UserPayment;
use App\BalanceHistory;
use Illuminate\Http\Request;
use App\Http\Traits\SaasAuthTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Events\GlobalSettingsChanged;
use App\Http\Traits\SaasPaymentTrait;
use App\Http\Helpers\InvoicePDFCreator;
use App\Http\Traits\PaymentPricesTrait;

class SubscriptionController extends Controller
{
    use SaasAuthTrait;
    use SaasPaymentTrait;
    use PaymentPricesTrait;

    //not used
    public function start_trial(Request $request)
    {
        $user = Auth::user();
        $res = (object)array();
        $res->errcode = 0;

        try{
            $updated_modules = [];
            foreach($request->input('modules') as $m)
            {
                $module = Module::find($m['id']);
                if ($module->trial_date_start || $module->current_subscription_date_start)
                {
                    continue;
                }
                $module->trial_date_start = Carbon::now();
                $module->trial_date_end = Carbon::now()->addMonth();
                $module->enabled = 1;
                $module->save();
                array_push($updated_modules, $module);
            }
            $res->data = $updated_modules;

            if (count($updated_modules) > 0)
            {
                $pseudo_payment = new stdClass;
                $pseudo_payment->created_at = Carbon::now()->toDateTimeString();
                $pseudo_payment->updated_at = Carbon::now()->toDateTimeString();
                $pseudo_payment->operator = 'trial';
                $pseudo_payment->payment_id = bin2hex(openssl_random_pseudo_bytes(16));
                $pseudo_payment->status = 'trial';
                $pseudo_payment->title = 'title';
                $pseudo_payment->sum = 0;
                $pseudo_payment->currency = 'trial';
                $pseudo_payment->payment_method = 'trial';
                $pseudo_payment->type = 3;
                $pseudo_payment->data = json_encode(array("modules" => $updated_modules));

                self::send_to_saas($pseudo_payment, self::get_access_token(), null, null);
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public function pay_from_balance(Request $request)
    {
        $user = Auth::user();
        $res = (object)array();
        $res->errcode = 0;

        $number_of_workers = $request->input('numberOfWorkers');
        $number_of_months = $request->input('numberOfMonths');
        // $amount = $request->input('amount');
        // $to_balance = $request->input('toBalance');
        $amount = self::total_sum($request->input('modules'), $number_of_workers, $number_of_months);
        $to_balance = self::to_balance($request->input('modules'), $number_of_workers, $number_of_months);

        try{
            $updated_modules = [];
            $setting_max_users =  Setting::where('key', '=', 'max_users')->first();
            $setting_max_users->value = $number_of_workers;
            $setting_max_users->save();

            $existingModules = Module::whereNotNull('current_subscription_date_start')->get();
            foreach($existingModules as $em)
            {
                if (!$updated_modules.contains('id', $em->id))
                {
                    $em->current_subscription_date_start = null;
                    $em->current_subscription_date_end = null;
                    $em->enabled = 0;
                    $em->save();
                }
            }

            foreach($request->input('modules') as $m)
            {
                $module = Module::find($m['id']);

                $module->current_subscription_date_start = Carbon::now();
                $module->current_subscription_date_end = Carbon::now()->addMonths($number_of_months);

                $module->enabled = 1;
                $module->save();
                array_push($updated_modules, $module);
            }
            $res->data = $updated_modules;

            $setting_balance =  Setting::where('key', '=', 'company_balance')->first();
            $bh = new BalanceHistory;
            $bh->transfer_amount = $to_balance;
            $bh->amount_before = $setting_balance->value;
            $setting_balance->value += $to_balance;
            $setting_balance->save();
            broadcast(new GlobalSettingsChanged());
            $bh->amount_after = $setting_balance->value;
            $bh->title = 'adding_after_recalculation';
            $bh->save();

            if (count($updated_modules) > 0)
            {
                $payment = new UserPayment;
                $payment->created_at = Carbon::now()/*->toDateTimeString()*/;
                $payment->updated_at = Carbon::now()/*->toDateTimeString()*/;
                $payment->operator = 'balance';
                $payment->payment_id = bin2hex(openssl_random_pseudo_bytes(16));
                $payment->status = 'balance';
                $payment->title = 'balance';
                $payment->sum = $amount;
                $payment->currency = 'balance';
                $payment->payment_method = 'balance';
                $payment->type = 4;
                $payment->data =  json_encode(array("modules" => $updated_modules, "from_balance" => $to_balance, "number_of_workers" => $number_of_workers));

                //if ($to_balance < 0)
                //{
                    $payment->sum = $to_balance * (-1);
                    $payment->save();
                //}

                self::send_to_saas($payment, self::get_access_token(), null, null);
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public function pay_with_invoice(Request $request)
    {
        $user = Auth::user();
        $res = (object)array();
        $res->errcode = 0;

        $number_of_workers = $request->input('numberOfWorkers');
        $number_of_months = $request->input('numberOfMonths');

        $amount = self::total_sum($request->input('modules'), $number_of_workers, $number_of_months);
        $to_balance = self::to_balance($request->input('modules'), $number_of_workers, $number_of_months);
        $currency = Setting::where('key', '=', 'price_currency')->first();
        $setting_balance =  Setting::where('key', '=', 'company_balance')->first();

        try{
            $updated_modules = [];

            foreach($request->input('modules') as $m)
            {
                $module = Module::find($m['id']);
                $module->current_subscription_date_start = Carbon::now();
                $module->current_subscription_date_end = Carbon::now()->addMonths($number_of_months);
                $module->enabled = 1;
                array_push($updated_modules, $module);
            }

            $payment = new UserPayment;
            $payment->created_at = Carbon::now()/*->toDateTimeString()*/;
            $payment->updated_at = Carbon::now()/*->toDateTimeString()*/;
            $payment->operator = 'invoice';
            $payment->payment_id = bin2hex(openssl_random_pseudo_bytes(16));
            $payment->status = 'invoice';
            $payment->title = 'invoice';
            $payment->sum = $amount;
            $payment->currency = $currency->value;
            $payment->payment_method = 'invoice';
            $payment->type = 2;
            $payment->data = json_encode(array("modules" => $updated_modules, "from_balance" => $setting_balance->value, "number_of_workers" => $number_of_workers));
            $payment->invoice_file_name = 'invoice.pdf';
            $payment->save();

            $pdf_response = '';
            $pdf_name = '';
            $creator = new InvoicePDFCreator;
            $format = $request->input('format', 'pdf');
            $type = $request->input('type', 'normal');
            $system = boolval($request->input('system', '0'));
            switch ($format) {
                case 'html':
                    $result = $creator->render_html(
                        null,
                        $request->input('invoiceCompanyName'),
                        $request->input('invoiceCountry'),
                        $request->input('invoiceCity'),
                        $request->input('invoiceAddress'),
                        $request->input('invoicePostCode'),
                        $amount,
                        $updated_modules,
                        $number_of_workers,
                        $payment->id
                    );
                    return Response($result);
                    break;
                case 'pdf':
                    $result =  $creator->render_pdf(
                        null, 
                        $system, 
                        $type, 
                        $request->input('invoiceCompanyName'),
                        $request->input('invoiceCountry'),
                        $request->input('invoiceCity'),
                        $request->input('invoiceAddress'),
                        $request->input('invoicePostCode'),
                        $amount,
                        $updated_modules,
                        $number_of_workers,
                        $payment->id
                    );
                    $pdf_response = $result["result"];
                    $pdf_name = $result["name"];
                    break;
            }

            $payment->invoice_file = $pdf_name;
            $payment->save();
         
            self::send_to_saas($payment, self::get_access_token(), $pdf_response, 'invoice.pdf');

            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Invoice.pdf"',
                'Accept-Ranges' => 'bytes',
                'Content-Length' => strlen($pdf_response)
            ];
            return Response($pdf_response, 200, $headers);

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public static function approve_payment_from_saas($payment_json)
    {
        $payment = json_decode($payment_json);
        $data = json_decode($payment->data);


        $modules = $data->modules;
        $from_balance = $data->from_balance;
        $number_of_workers = $data->number_of_workers;

        $setting_balance =  Setting::where('key', '=', 'company_balance')->first();
        $setting_max_users =  Setting::where('key', '=', 'max_users')->first();

        try{
            $actual_payment = UserPayment::where('payment_id', '=', $payment->payment_id)->firstOrFail();
            if ($actual_payment == null) {throw new Exception('Payment not found during approving '.$payment->payment_id);}

            $existingModules = Module::whereNotNull('current_subscription_date_start')->get();
            foreach($existingModules as $em)
            {
                $em->current_subscription_date_start = null;
                $em->current_subscription_date_end = null;
                $em->enabled = 0;
                $em->save();
            }

            foreach($modules as $m)
            {
                $module = Module::find($m->id);
                $module->current_subscription_date_start = $m->current_subscription_date_start;
                $module->current_subscription_date_end = $m->current_subscription_date_end;
                $module->enabled = 1;
                $module->save();
            }

            $actual_payment->status = 'approved';
            $actual_payment->save();

            $setting_max_users->value = $number_of_workers;
            $setting_max_users->save();

            if ($setting_balance->value > 0)
            {
                $bh = new BalanceHistory;
                $bh->transfer_amount = $setting_balance->value;
                $bh->amount_before = $setting_balance->value;
                $setting_balance->value = 0;
                $setting_balance->save();                
                $bh->amount_after = $setting_balance->value;
                $bh->title = 'substracting_after_payment';
                $bh->save();
            }
            broadcast(new GlobalSettingsChanged());
        } catch (Exception $e) {
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
    }
}
