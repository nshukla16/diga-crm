<?php
namespace App\Http\Traits;

use Exception;
use Log;

trait SaasPricesTrait 
{
    private static function get_from_saas($response_decoded)
    {
        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->get(env('ERP_SAAS_URL', '').'/api/integrations/prices/get', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$response_decoded['access_token'],
            ],
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded['result'];
    }
}
