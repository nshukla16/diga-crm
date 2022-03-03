<?php

namespace App\Http\Controllers;

use Exception;
use App\Module;
use App\Setting;
use Carbon\Carbon;
use App\UserPayment;
use Braintree\Gateway;
use App\BalanceHistory;
use Illuminate\Http\Request;
use App\Http\Traits\SaasAuthTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Events\GlobalSettingsChanged;
use App\Http\Traits\SaasPaymentTrait;
use App\Http\Traits\PaymentPricesTrait;
use App\Http\Controllers\SubscriptionController;

class BrainTreeController extends Controller
{
    use SaasAuthTrait;
    use SaasPaymentTrait;
    use PaymentPricesTrait;

    protected $gateway;

    public function __construct()
    {
        $this->gateway = new Gateway([
            'environment' => env('BRAINTREE_ENVIRONMENT'),
            'merchantId' => env('BRAINTREE_MERCHANTID'),
            'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
            'privateKey' => env('BRAINTREE_PRIVATE_KEY')
        ]);
    }

    public function _token()
    {
        $user = Auth::user();

        $clientToken = $this->gateway->clientToken()->generate([
            // "customerId" => $user->id
        ]);

        return $clientToken;
    }

    public function receive_nonce(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        $user = Auth::user();

        $nonceFromTheClient = $request->input('nonce');
        $number_of_workers = $request->input('numberOfWorkers');
        $number_of_months = $request->input('numberOfMonths');

        // $amount = $request->input('amount');
        $amount = self::total_sum($request->input('modules'), $number_of_workers, $number_of_months);

        $result = $this->gateway->transaction()->sale([
            'amount' => $amount,
            'paymentMethodNonce' => $nonceFromTheClient,
            'options' => [
              'submitForSettlement' => True
            ]
          ]);

        if ($result->success)
        {
            $transaction = $result->transaction;

            $setting_balance =  Setting::where('key', '=', 'company_balance')->first();

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
                if ($module->current_subscription_date_start && $module->current_subscription_date_end)
                {
                    $isSubscriptionOn = Carbon::now() >= Carbon::parse($module->current_subscription_date_start) &&
                        Carbon::now() <= Carbon::parse($module->current_subscription_date_end);
                    if ($isSubscriptionOn == true)
                    {
                        $has_subscription_flag = true;
                        $module->current_subscription_date_end = Carbon::parse($module->current_subscription_date_end)->addMonths($number_of_months);
                    }
                    else
                    {
                        $module->current_subscription_date_start = Carbon::now();
                        $module->current_subscription_date_end = Carbon::now()->addMonths($number_of_months);
                    }
                }
                else
                {
                    $module->current_subscription_date_start = Carbon::now();
                    $module->current_subscription_date_end = Carbon::now()->addMonths($number_of_months);
                }
                $module->enabled = 1;
                $module->save();
                array_push($updated_modules, $module);
            }
            $res->data = $updated_modules;

            $user_payment = new UserPayment;
            $user_payment->created_at = Carbon::now()->toDateTimeString();
            $user_payment->updated_at = Carbon::now()->toDateTimeString();
            $user_payment->user_id = $user->id;
            $user_payment->operator = 'braintree';
            $user_payment->payment_id = $transaction->id;
            $user_payment->status = $transaction->status;
            $user_payment->title = 'payment';
            $user_payment->sum = $transaction->amount;
            $user_payment->currency = $transaction->currencyIsoCode;
            $user_payment->payment_method = $transaction->paymentInstrumentType;
            $user_payment->type = 1;
            $user_payment->data = json_encode(array("modules" => $updated_modules, "from_balance" => $setting_balance->value, "number_of_workers" => $number_of_workers));

            $user_payment->save();

            self::send_to_saas($user_payment, self::get_access_token(), null, null);
            
            if ($setting_balance->value > 0)
            {
                $bh = new BalanceHistory;
                $bh->transfer_amount = $setting_balance->value;
                $bh->amount_before = $setting_balance->value;
                $setting_balance->value = 0;
                $setting_balance->save();
                broadcast(new GlobalSettingsChanged());
                $bh->amount_after = $setting_balance->value;
                $bh->title = 'substracting_after_payment';
                $bh->save();
            }
        }
        else
        {
            $errors = '';
            foreach($result->errors->deepAll() AS $error) {
                $errors += print_r($error->attribute . ": " . $error->code . " " . $error->message . "\n");
            }

            $res->errcode = 1;
            $res->errmess = $errors;
            Log::channel('daily')->error($errors);
            app('sentry')->captureException(new Exception($errors));
        }

        return response()->json($res);
    }
}
