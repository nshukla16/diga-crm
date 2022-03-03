<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\Throw_;

trait PlatformTrait
{
    public static function post_service($token, $service, $sub)
    {
        $guzzle = new \GuzzleHttp\Client;
        $response = $guzzle->post(env('PLATFORM_URL', '') . '/api/erp/platform/contracts/' . $sub, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($service)
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        return $response_decoded;
    }
}
