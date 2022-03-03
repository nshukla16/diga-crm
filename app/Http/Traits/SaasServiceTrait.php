<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait SaasServiceTrait
{
    private static function create_service($url, $source_url, $service, $estimate_group, $estimate, $client_contact, $token)
    {
        $data = [
            'url' => $url,
            'source_url' => $source_url,
            'service' => $service,
            'estimate_group' => $estimate_group,
            'estimate' => $estimate,
            'client_contact' => $client_contact
        ];

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/erp-service', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'form_params' => $data
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['errcode'] == 0;
    }

    private static function update_service_status_from_contractor($url, $source_url, $source_id, $status, $token)
    {
        $data = [
            'url' => $url,
            'source_url' => $source_url,
            'source_id' => $source_id,
            'status' => $status,
        ];

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/erp-service/change-status-from-contractor', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'form_params' => $data
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['errcode'] == 0;
    }

    private static function update_service_status_from_general_contractor($url, $source_url, $service_id, $status, $contractor_decline_reason, $token)
    {
        $data = [
            'url' => $url,
            'source_url' => $source_url,
            'source_id' => $service_id,
            'status' => $status,
            'contractor_decline_reason' => $contractor_decline_reason
        ];

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/erp-service/change-status-from-general-contractor', [
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
