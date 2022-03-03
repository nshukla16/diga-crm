<?php
namespace App\Http\Traits;

use Exception;
use Log;

trait SaasAuthTrait {
    private static function get_access_token()
    {
        $guzzle = new \GuzzleHttp\Client;

        $response = $guzzle->post(env('ERP_SAAS_URL', '').'/oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('ERP_SAAS_CLIENT_ID', ''),
                'client_secret' => env('ERP_SAAS_SECRET', '')
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
