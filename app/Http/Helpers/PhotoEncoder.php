<?php

namespace App\Http\Helpers;

use Exception;
use Carbon\Carbon;
use App\GlobalSettings;
use App\ObjectStore;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;

class PhotoEncoder
{
    public static function get_photo_encodings($image)
    {
        $guzzle = new \GuzzleHttp\Client;

        $data = [
            [
                'name'     => 'file',
                'contents' => file_get_contents(public_path(substr($image, 1))),
                'filename' => substr($image, strrpos($image, '/')+1)
            ],
        ];

        $response = $guzzle->request('POST', env('ERP_FACE_RECOGNITION_URL', '127.0.0.1:5000').'/face_encodings', [
            'multipart' => $data
        ]);

        $response_decoded = json_decode((string) $response->getBody(), true);

        if ($response_decoded['result'] == 'ok'){
            return substr(substr($response_decoded['data'], 1), 0, -1);
        }else{
            return null;
        }
    }
}
