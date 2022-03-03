<?php

namespace App\Http\Controllers\Api\v1;

use App\User;
use App\CheckfrontField;
use App\Events\GlobalSettingsChanged;
use App\FacebookPage;
use App\GlobalSettings;
use Illuminate\Support\Facades\File;
use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Rkesa\Service\Models\ServiceState;

class GlobalSettingsController extends Controller
{
    public function index(Request $request)
    {
        $fields = $request->input('fields', '*');
        $fields_array = explode(",", $fields);
        $fields_array_without_relations = array_filter($fields_array, function($el){
            return !in_array($el, ['erp_version', 'timezone', 'smtp_settings']);
        });
        $gs = GlobalSettings::select($fields_array_without_relations)->first();

        if (in_array('erp_version', $fields_array) || $fields == '*') {
            $version = 'UNKNOWN';
            try {
                $version = File::get(base_path() . '/VERSION');
            } catch (Exception $exception) {}
            $gs->erp_version = $version;
        }

        if (in_array('timezone', $fields_array) || $fields == '*') {
            $tz = env('APP_TIMEZONE');
            if ($tz == 'UTC') {
                $tz = 'Etc/GMT';
            }
            $gs->timezone = $tz;
        }

        if (in_array('smtp_settings', $fields_array) || $fields == '*') {
            if (env('MAIL_HOST') == env('DEFAULT_MAIL_HOST')
                && env('MAIL_USERNAME') == env('DEFAULT_MAIL_USERNAME')
                && env('MAIL_PASSWORD') == env('DEFAULT_MAIL_PASSWORD')){
                    $smtp_settings = [];
                }
            else{
                $smtp_settings = [
                    'mail_host' => env('MAIL_HOST', ''),
                    'mail_port' => env('MAIL_PORT', ''),
                    'mail_username' => env('MAIL_USERNAME', ''),
                    'mail_password' => env('MAIL_PASSWORD', ''),
                    'mail_encryption' => env('MAIL_ENCRYPTION', ''),
                ];
            }
            $gs->smtp_settings = $smtp_settings;
        }

        // $gs->main_web_site = env('MAIN_WEB_SITE');

        $gs->checkfront_fields = CheckfrontField::all();
        $gs->fb_pages = FacebookPage::all()->toArray();
        return $gs;
    }

    public function global_save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $vals = $request->all();
            $gs = GlobalSettings::first();
            $gs->fill($vals);
            if ($vals['site_logo'] !== $gs->site_logo) {
                // remove old
                if ($gs->site_logo != '/img/logo.png') {
                    $old_filepath = public_path() . $gs->site_logo;
                    if (file_exists($old_filepath)) {
                        unlink($old_filepath);
                    }
                }
                // copy new from temp
                $old_file = substr($vals['site_logo'], 1);
                $fileext = pathinfo($vals['site_logo'], PATHINFO_EXTENSION);
                $new_file = 'img/uploads/global/logo.' . $fileext;
                rename($old_file, $new_file);
                $gs->site_logo = '/' . $new_file . '?t=' . filemtime($new_file);
            }
            $gs->save();

            if ($gs->use_default_smtp){
                $this->setEnvironmentValue('MAIL_HOST', env('DEFAULT_MAIL_HOST', ''));
                $this->setEnvironmentValue('MAIL_PORT', env('DEFAULT_MAIL_PORT', ''));
                $this->setEnvironmentValue('MAIL_USERNAME', env('DEFAULT_MAIL_USERNAME', ''));
                $this->setEnvironmentValue('MAIL_PASSWORD', env('DEFAULT_MAIL_PASSWORD', ''));
                $this->setEnvironmentValue('MAIL_ENCRYPTION', env('DEFAULT_MAIL_ENCRYPTION', ''));
            } else {
                $this->setEnvironmentValue('MAIL_HOST', $vals['smtp_settings']['mail_host']);
                $this->setEnvironmentValue('MAIL_PORT', $vals['smtp_settings']['mail_port']);
                $this->setEnvironmentValue('MAIL_USERNAME', $vals['smtp_settings']['mail_username']);
                $this->setEnvironmentValue('MAIL_PASSWORD', $vals['smtp_settings']['mail_password']);
                $this->setEnvironmentValue('MAIL_ENCRYPTION', $vals['smtp_settings']['mail_encryption']);    
            }

            $this->setEnvironmentValue('APP_TIMEZONE', $vals['timezone']);
            broadcast(new GlobalSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    // https://stackoverflow.com/questions/40450162/how-to-set-env-values-in-laravel-programmatically-on-the-fly
    private function setEnvironmentValue($envKey, $envValue)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $oldValue = env($envKey);

        $str = str_replace("{$envKey}={$oldValue}", "{$envKey}={$envValue}", $str);

        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);
    }
}
