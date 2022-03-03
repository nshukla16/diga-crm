<?php

namespace Rkesa\Client\Http\Helpers;

use App\Http\Traits\Auth0Trait;
use App\Http\Traits\PlatformTrait;
use Illuminate\Support\Facades\Log;

class PlatformHelper
{
    use Auth0Trait;
    use PlatformTrait;

    public function transfer_to_platform($service, $sub)
    {
        $management = self::get_platform_machine_token();
        $token = $management->access_token;

        $contract = self::post_service(
            $token,
            array(
                'name' => $service->name,
                'address' => $service->address,
                'plannedBuildStart' => $service->work_start,
                'plannedBuildEnd' => $service->work_end,
                'estimate' => $service->estimate,
                'domain' => env('APP_URL')
            ),
            $sub
        );
        return $contract;
    }
}
