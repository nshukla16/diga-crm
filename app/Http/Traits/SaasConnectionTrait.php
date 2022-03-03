<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait SaasConnectionTrait
{
    private static function check_if_exists($url, $token)
    {
        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->get(env('ERP_SAAS_URL', '') . '/api/erp/exists?url=' . $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ]
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['errcode'] == 0;
    }

    private static function create($url, $source_url, $token)
    {
        $data = [
            'url' => $url,
            'source_url' => $source_url,
        ];

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/erp', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'form_params' => $data
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['errcode'] == 0;
    }

    private static function confirmation($url, $source_url, $token)
    {
        $data = [
            'url' => $url,
            'source_url' => $source_url,
        ];

        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('ERP_SAAS_URL', '') . '/api/erp/confirm', [
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
