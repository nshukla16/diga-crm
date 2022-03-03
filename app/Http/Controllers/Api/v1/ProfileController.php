<?php

namespace App\Http\Controllers\Api\v1;

use Log;
use Auth;
use App\User;
use Exception;
use App\GlobalSettings;
use Illuminate\Http\Request;
use App\Events\ProfileChanged;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Helpers\TelegramMadelineProto;
use App\UserDevice;

include_once base_path('packages/Rkesa/Email/vendor/afterlogic_webmail/libraries/afterlogic/api.php');

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user1 = User::with('estimate_group_workers')->find($request->user()->id)->makeVisible('email_password');
        return $user1;
    }

    public function schedule(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'day_start_time' => $user->day_start_time,
            'lunch_time' => $user->lunch_time,
            'day_finish_time' => $user->day_finish_time,
            'working_days' => $user->working_days,
        ]);
    }

    public function save_schedule(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();

            $user->day_start_time = $request['day_start_time'];
            $user->lunch_time = $request['lunch_time'];
            $user->day_finish_time = $request['day_finish_time'];
            $user->working_days = json_encode($request['working_days']);
            $user->save();
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function add_device(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();

            $token = $request['token'];
            $device_type = $request['device_type'];

            if ($token != null && $device_type != null) {
                $device = new UserDevice;
                $device->token = $token;
                $device->device_type = $device_type;
                $device->user_id = $user->id;
                $device->save();
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function remove_device(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $user = Auth::user();

            $token = $request['token'];

            if ($token != null) {
                $device = UserDevice::where('token', $token)->where('user_id', $user->id)->first();
                if ($device != null) {
                    $device->delete();
                }
            }
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function update(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try {
            $old_password = $request->input('old_password', null);
            $new_password = $request->input('new_password', null);
            $user = Auth::user();
            $user_id = $user->id;
            if ($old_password != "" || $new_password != "") {
                if (Hash::check($old_password, $user->password)) {
                    if ($new_password != "" && strlen($new_password) >= 8) {
                        $obj_user = User::find($user_id);
                        $obj_user->password = Hash::make($new_password);
                        $obj_user->save();
                    } else {
                        $res->errcode = 1;
                        $res->errmess = trans('template.New_password_incorrect');
                    }
                } else {
                    $res->errcode = 1;
                    $res->errmess = trans('template.Current_password_incorrect');
                }
            }
            $email_password = $request->input('profile.email_password', null);
            $obj_user = User::find($user_id);
            $obj_user->email_password = $email_password;
            $obj_user->gc_enabled = boolval($request->input('profile.gc_enabled', "0"));
            $obj_user->site_language = $request->input('profile.site_language', 'en');
            $obj_user->cell_phone = $request->input('profile.cell_phone', null);
            $obj_user->formatted_cell_phone = $request->input('profile.formatted_cell_phone', null);
            $obj_user->tg_username = $request->input('profile.tg_username', null);
            $obj_user->show_calendar_on_main_page = $request->input('profile.show_calendar_on_main_page', null);
            $obj_user->autosave_estimates = $request->input('profile.autosave_estimates', null);

            $gs = GlobalSettings::first();
            if ($gs->telegram_enabled == true && strlen($obj_user->formatted_cell_phone) > 0) {
                $tg_helper = new TelegramMadelineProto;
                $first_name = "";
                $last_name = "";
                $pieces = explode(" ", $obj_user->name);
                if (count($pieces) > 1) {
                    $first_name = $pieces[0];
                    $last_name = $pieces[1];
                } else {
                    $first_name = $obj_user->name;
                    $last_name = $obj_user->name;
                }
                $tg_id = $tg_helper->add_to_contacts($obj_user->tg_username, $first_name, $last_name, $obj_user->formatted_cell_phone);

                if ($tg_id > 0) {
                    $obj_user->tg_id = $tg_id;
                }
            } 
            self::change_webmail_language($obj_user->site_language);
            $obj_user->save();
            broadcast(new ProfileChanged($obj_user));
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    private function change_webmail_language($language)
    {
        try {
            if (class_exists('CApi') && \CApi::IsValid()) {
                $sEmail = Auth::user()->email;
                $sPassword = Auth::user()->email_password;

                $oApiIntegratorManager = \CApi::Manager('integrator');
                $oAccount = $oApiIntegratorManager->LoginToAccount($sEmail, $sPassword);

                if ($oAccount) {
                    $oApiIntegratorManager->SetAccountAsLoggedIn($oAccount);
                    switch ($language) {
                        case 'ru':
                            $oAccount->User->DefaultLanguage = 'Russian';
                            break;
                        case 'pt':
                            $oAccount->User->DefaultLanguage = 'Portuguese-Portuguese';
                            break;
                        case 'en':
                            $oAccount->User->DefaultLanguage = 'English';
                            break;
                    }
                    $oApiUsersManager = \CApi::Manager('users');
                    $oApiUsersManager->updateAccount($oAccount);
                }
            }
        } catch (Exception $e) {
        } // if there is no webmail available -> don't raise error, we only wanted to try
    }
}
