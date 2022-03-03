<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait SaasPayStageTrait
{
    private static function upload_invoice_from_contractor($url, $source_url, $source_id, $invoice_file, $invoice_file_name, $token)
    {
        $data = [
            'url' => $url,
            'source_url' => $source_url,
            'source_id' => $source_id,
            'invoice_file' => $invoice_file,
            'invoice_file_name' => $invoice_file_name,
        ];

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/erp-paystage/upload-invoice-from-contractor', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'form_params' => $data
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['errcode'] == 0;
    }

    private static function paid_from_general_contractor($url, $source_url, $source_id, $fact_paid, $token)
    {
        $data = [
            'url' => $url,
            'source_url' => $source_url,
            'source_id' => $source_id,
            'fact_paid' => $fact_paid,
        ];

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/erp-paystage/paid-from-general-contractor', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'form_params' => $data
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['errcode'] == 0;
    }
}
