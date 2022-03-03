<?php
namespace App\Http\Traits;

use Exception;
use Log;

trait SaasPaymentTrait {
    private static function send_to_saas($payment, $response_decoded, $invoice, $invoice_name)
    {
        $data = [
            'created_at' => $payment->created_at->toDateTimeString(),
            'updated_at' => $payment->updated_at->toDateTimeString(),
            'operator' => $payment->operator,
            'payment_id' => $payment->payment_id,
            'status' => $payment->status,
            'title' => $payment->title,
            'sum' => $payment->sum,
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'data' => $payment->data,
            'type' => $payment->type,
            'invoice_file_name' => isset($payment->invoice_file_name) ? $payment->invoice_file_name : NULL,
        ];

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/integrations/subscriptions/receive', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'multipart' => [
                [
                    'name'     => 'FileContents',
                    'contents' =>  $invoice,
                    'filename' => $invoice_name,
                ],
                [
                    'name' => 'data',
                    'contents' => json_encode($data)
                ]
            ]
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['result'] == 'OK';
    }
}
