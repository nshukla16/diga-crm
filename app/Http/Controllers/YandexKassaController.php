<?php

namespace App\Http\Controllers;

use App\UserPayment;
use Illuminate\Http\Request;
use YandexCheckout\Model\Notification\NotificationSucceeded;
use YandexCheckout\Model\Notification\NotificationWaitingForCapture;
use YandexCheckout\Model\NotificationEventType;

class YandexKassaController extends Controller
{
    protected $list_of_ips = array ('185.71.76.0/27', '185.71.77.0/27', 
    '77.75.153.0/25', '77.75.154.128/25', 
    '2a02:5180:0:1509::/64', '2a02:5180:0:2655::/64',
    '2a02:5180:0:1533::/64', '2a02:5180:0:2669::/64');

    public function pay(Request $request)
    {
        $user = Auth::user();
        $res = (object)array();
        $res->errcode = 0;
        try{
            $client = new Client();
            $client->setAuth('<Идентификатор магазина>', '<Секретный ключ>');
            $payment = $client->createPayment(
                array(
                    'amount' => array(
                        'value' => 1.0,
                        'currency' => 'RUB',
                    ),
                    'confirmation' => array(
                        'type' => 'redirect',
                        'return_url' => env('APP_URL', 'http://localhost') +  'payment/return_url',
                    ),
                    'capture' => true,
                    'description' => 'Order for Diga subscription',
                ),
                uniqid('', true)
            );

            $client->addWebhook([
                "event" => NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE,
                "url"   => env('APP_URL', 'http://localhost') + "/yandex-kassa/webhook", 
            ]);
            $client->addWebhook([
                "event" => NotificationEventType::PAYMENT_SUCCEEDED,
                "url"   => env('APP_URL', 'http://localhost') + "/yandex-kassa/webhook", 
            ]);
            $client->addWebhook([
                "event" => NotificationEventType::PAYMENT_CANCELED,
                "url"   => env('APP_URL', 'http://localhost') + "/yandex-kassa/webhook", 
            ]);
            $client->addWebhook([
                "event" => NotificationEventType::REFUND_SUCCEEDED,
                "url"   => env('APP_URL', 'http://localhost') + "/yandex-kassa/webhook", 
            ]);

            $res->payment_data = $payment;

            $our_payment = new UserPayment;
            $our_payment->user_id = $user->id;
            $our_payment->operator = 'yandex_kassa';
            $our_payment->payment_id = $payment->id;
            $our_payment->status = $payment->status;
            $our_payment->title = 'Order for Diga subscription';
            $our_payment->sum = 1.0;
            $our_payment->currency = 'RUB';
            $our_payment->sum = 1.0;
            $our_payment->save();

        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }

        return response()->json($res);
    }

    public function webhook(Request $request)
    {
        if (in_array($request->ip(), $this->list_of_ips)){
            $res = (object)array();
            $res->errcode = 0;

            try {
                $requestBody = json_decode($request, true);

                $notification = null;
                switch ($requestBody['event'])
                {
                    case NotificationEventType::PAYMENT_SUCCEEDED:
                        $notification = new NotificationSucceeded($requestBody);
                        //todo
                        break;
                    case NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE:
                        $notification = new NotificationWaitingForCapture($requestBody);
                        //todo
                        break;
                    case NotificationEventType::PAYMENT_CANCELED:
                        $notification = new NotificationCanceled($requestBody);
                        //todo
                        break;
                    case NotificationEventType::REFUND_SUCCEEDED:
                        $notification = new NotificationRefundSucceeded($requestBody);
                        //todo
                        break;
                }

                $our_payment = UserPayment::where('payment_id', '=', $requestBody['object']['id'])->first();
                $our_payment->status = $requestBody['event'];
                $our_payment->save();

            } catch (Exception $e) {
                $res->errcode = 1;
                $res->errmess = $e->getMessage();
                Log::channel('daily')->error($e);
                app('sentry')->captureException($e);
            }
            return response()->json($res);
        }
    }
}
