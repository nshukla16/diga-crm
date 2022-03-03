<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\Auth0Trait;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Auth0Controller extends Controller
{
    use Auth0Trait;

    public function token(Request $request)
    {
        $res = (object)array();
        $code = $request->input('code', 10);
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://diga.eu.auth0.com/oauth/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "grant_type=authorization_code&client_id=" . env('AUTH0_CLIENT_ID') . "&client_secret=" . env('AUTH0_CLIENT_SECRET') . "&code=" . $code . "&redirect_uri=" . urlencode(env("APP_URL") . '/auth0'),
                CURLOPT_HTTPHEADER => [
                    "content-type: application/x-www-form-urlencoded"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return response()->json(["message" => "Error while getting token. " . $err], 401);
            } else {
                $result = json_decode($response);
                $res = $result;
                $user_info = self::get_user_info($result->access_token);

                $user = User::where('sub', $user_info->sub)->first();
                if ($user == null) {
                    return response()->json(["message" => "Unauthorized user"], 401);
                    // $user = new User;

                    // $user->sub = $user_info->sub;
                    // $user->email = $user_info->email;
                    // $user->name = $user_info->name;
                    // $user->active = false;
                    // $user->photo = '/img/no_profile_picture.png';

                    // $pins = User::pluck('pin')->toArray();
                    // while (true) {
                    //     $pin = rand(1000, 9999);
                    //     if (in_array($pin, $pins)) {
                    //         continue;
                    //     } else {
                    //         $user->pin = $pin;
                    //         break;
                    //     }
                    // }

                    // $user->save();
                }
            }
        } catch (\Exception $e) {
            return response()->json(["message" => "Code is incorrect"], 401);
        }
        return response()->json($res);
    }
}
