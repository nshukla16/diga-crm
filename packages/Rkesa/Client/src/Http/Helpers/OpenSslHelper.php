<?php

namespace Rkesa\Client\Http\Helpers;

use Exception;
use Carbon\Carbon;
use App\GlobalSettings;
use App\CompanyInformation;
use Illuminate\Support\Str;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Log;
use App\Events\SitesSettingsChanged;
use Illuminate\Support\Facades\Storage;
use Rkesa\Estimate\Models\EstimateLine;
use Rkesa\Estimate\Models\EstimateUnit;
use Illuminate\Support\Facades\Response;

class OpenSslHelper
{
    public function sign($data)
    {
        $pkeyid = openssl_pkey_get_private(Storage::get('ChavePrivada.txt'));
        $pubkeyid = openssl_get_publickey(Storage::get('ChavePublica.txt'));

        if ($pkeyid === false){
            throw new Exception("Error loading private key");
        }

        if ($pubkeyid === false){
            throw new Exception("Error loading public key");
        }
        
        $binary_signature = "";
        
        $signed = openssl_sign($data, $binary_signature, $pkeyid, OPENSSL_ALGO_SHA1);
        if (!$signed){
            throw new Exception("Error while signing");
        }

        // openssl_free_key($pkeyid);
     
        $ok = openssl_verify($data, $binary_signature, $pubkeyid, OPENSSL_ALGO_SHA1);

        // openssl_free_key($pubkeyid);
        
        if ($ok != 1) {
            throw new Exception("Error while verifying");
        }

        $base64 = base64_encode($binary_signature);

        return $base64;
    }
}
