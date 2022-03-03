<?php

namespace App\Http\Controllers;

use Exception;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Edujugon\GoogleAds\GoogleAds;
use Edujugon\GoogleAds\Auth\OAuth2;
use Illuminate\Support\Facades\Log;
use Google\AdsApi\AdWords\v201809\cm\CampaignService;

class GoogleAdsController extends Controller
{
    public function get_refresh_token(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $oAth2 = (new OAuth2())->build();
        
            $accessToken = $request['code'];

            $oAth2->setCode($accessToken);
            $authToken = $oAth2->fetchAuthToken();

            if (!array_key_exists('refresh_token', $authToken)) {
                throw new Exception('Google Ads: Couldn\'t find refresh_token key in the response. ' . json_encode($authToken));
            }
            $res->refresh_token = $authToken;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function campaigns(Request $request){
        $res = (object)array();
        $res->errcode = 0;
        try{
            $ads = new GoogleAds();
            $gs = GlobalSettings::first();
            $ads->env('test')
            ->oAuth([
                'clientId' => $gs->google_ads_client_id,
                'clientSecret' => $gs->google_ads_client_secret,
                'refreshToken' => $gs->google_ads_refresh_token        
            ])
            ->session([
                'developerToken' => $gs->google_ads_developer_token,
                'clientCustomerId' => $gs->google_ads_client_customer_id
            ]);

            $campaigns = $ads->service(CampaignService::class)
                ->select(['Id', 'Name', 'Status', 'ServingStatus', 'StartDate', 'EndDate'])
                ->get();

            $res->rows = $campaigns;
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }
}
