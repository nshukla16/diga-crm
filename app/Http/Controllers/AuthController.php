<?php

namespace App\Http\Controllers;

use Log;
use App\GlobalSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class AuthController extends Controller
{
    const WEB_CLIENT = 'ERP Password Grant Client';
    const MOBILE_CLIENT = 'ERP Mobile Password Grant Client';

    private function issueToken(Request $request, $grantType, $scope = "*"){
        $client_mobile = $request->input('mobile', '0') == '1';

        if ($client_mobile) {
            $client = \Laravel\Passport\Client::where('name', $this::MOBILE_CLIENT)->first();
        }else{
            $client = \Laravel\Passport\Client::where('name', $this::WEB_CLIENT)->first();
        }

        if (!$client) {
            return response()->json([
                'message' => 'Laravel Passport is not setup properly.',
                'status' => 500
            ], 500);
        }

        $params = [
            'grant_type' => $grantType,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => $scope,
        ];

        $request->request->add($params);

        $proxy = Request::create('oauth/token', 'POST');

        return Route::dispatch($proxy);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        return $this->issueToken($request, 'password');
    }

    public function login_with_pin(Request $request)
    {
        $this->validate($request, [
            'pin' => 'required',
        ]);

        return $this->issueToken($request, 'pin_grant');
    }

    public function refresh(Request $request)
    {
        $this->validate($request, [
            'refresh_token' => 'required'
        ]);

        return $this->issueToken($request, 'refresh_token');
    }

    public function logout(Request $request)
    {
        $accessToken = auth()->user()->token();

        $refreshToken = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke();

        return response()->json(['status' => 200]);
    }

    public function shared_info(Request $request){
        $gs = GlobalSettings::first();
        return response()->json(['url' => $gs->site_logo, 'language' => $gs->default_language]);
    }

    public function version(Request $request){
        $erp_version = trim(file_get_contents(base_path('VERSION')));
        return response()->json(['ver' => $erp_version]);
    }
}
