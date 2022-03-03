<?php
namespace App\Http\Traits;

use GuzzleHttp\Client;

trait SMSTrait {
    public static function send_sms($data)
    {
        $guzzle = new \GuzzleHttp\Client;

        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('ERP_SAAS_CLIENT_ID', ''),
                'client_secret' => env('ERP_SAAS_SECRET', '')
            ],
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/api/send_sms', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
            'form_params' => $data
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
