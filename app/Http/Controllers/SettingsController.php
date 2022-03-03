<?php

namespace App\Http\Controllers;

use App\AuthPhoto;
use App\CheckfrontField;
use App\Events\GlobalSettingsChanged;
use App\Events\SitesSettingsChanged;
use App\FacebookPage;
use App\GlobalSettings;
use App\Setting;
use App\Site;
use Illuminate\Http\Request;
use App\User;
use Exception;
use Illuminate\Support\Facades\File;
use Log;
use Rkesa\Calendar\Models\EventType;
use Rkesa\Client\Models\ClientHistory;
use Rkesa\Service\Models\ServiceState;
use App\Http\Controllers\Zadarma\Number as ZadarmaNumber;
use App\Http\Controllers\Zadarma\Api as ZadarmaApi;

class SettingsController extends Controller
{
    public function site_save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            foreach($request['removed_sites'] as $i){
                $r = Site::find($i);
                if ($r){
                    $ch = ClientHistory::where('site_id', $r->id)->get();
                    foreach($ch as $h){
                        $h->site_id = null;
                        $h->save();
                    }
                    $r->delete();
                }
            }
            foreach($request['sites'] as $i){
                if ($i['id'] == 0){
                    $r = new Site;
                    $r->domain = $i['domain'];
                    $r->token = str_replace('.', '-', uniqid('', true));
                    $r->save();
                }else {
                    $r = Site::find($i['id']);
                    $r->domain = $i['domain'];
                    $r->save();
                }
            }
            $gs = GlobalSettings::first();
            $gs->fill($request['global_settings']);
            $gs->save();
            broadcast(new SitesSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function auth_photos(Request $request)
    {
        return view('settings/auth_photos', [
            'auth_photos' => AuthPhoto::all()->toJson(),
        ]);
    }

    public function integration_save(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            $settings = $request['global_settings'];
            $gs = new GlobalSettings;
            $fields = $gs->getFillable();

            $zadarma_fields = array_filter($fields, function($z) {
                return strpos($z, 'zadarma') !== false;
            });

            foreach($zadarma_fields as $zf)
                if(!array_key_exists($zf, $settings))
                    throw new Exception(trans('template.Server_error'));

            $gs = $gs::first();
            $gs->fill($settings);

            if ($settings['fb_enabled'] == false){
                $gs->fb_enabled = false;
                foreach($settings['fb_pages'] as $i => $page) {
                    $settings['fb_pages'][$i]['enabled'] = false;
                }
            }
            $gs->save();

            // foreach($settings['fb_pages'] as $page) {
            //     $fb_page = FacebookPage::where('page_id', $page['page_id'])->first();
            //     $fb_page->enabled = $page['enabled'];
            //     $fb_page->save();
            //     if ($fb_page->enabled){
            //         $result = FacebookController::subscribe_page($fb_page->page_id);
            //         if ($result['success']){
            //             Log::info('subscribed');
            //         }else{
            //             Log::info('ERROR');
            //         }
            //     }else{
            //         $result = FacebookController::unsubscribe_page($fb_page->page_id);
            //         if ($result['success']){
            //             Log::info('unsubscribed');
            //         }else{
            //             Log::info('ERROR');
            //         }
            //     }
            // }

            // Checkfront
            foreach($settings['checkfront_removed_fields'] as $i){
                $field = CheckfrontField::find($i);
                if ($field){
                    $field->delete();
                }
            }
            foreach($settings['checkfront_fields'] as $i){
                if ($i['id'] == 0){
                    CheckfrontField::create($i);
                }else {
                    CheckfrontField::find($i['id'])->update($i);
                }
            }

            if($settings['zadarma_enabled'] == true) {
                $api = new ZadarmaApi();

                if($api->isAuthorized()) {
                    $numbers = new ZadarmaNumber();
                    $numbers->insertNumbers();

                    $rusers = $settings['responsible_users'];
                    foreach($rusers as $ruser) {
                        $user = User::where('id', $ruser['id'])->first();
                        $user->zadarma_internal_phonecode = $ruser['zadarma_internal_phonecode'];
//                        $user->zadarma_default_responsible = $ruser['zadarma_default_responsible'];
                        $user->save();
                    }
                } else
                    throw new Exception(trans('zadarma.AuthFailed'));
            }

            //google ads
            // if ($settings['google_ads_enabled'] == true){
            //     $res->url = "https://accounts.google.com/o/oauth2/v2/auth?response_type=code&access_type=offline&client_id=". $settings['google_ads_client_id'] ."&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&state&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fadwords";

            // }

            broadcast(new GlobalSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::channel('daily')->error($e);
            app('sentry')->captureException($e);
        }
        return response()->json($res);
    }

    public function save_settings(Request $request)
    {
        $res = (object)array();
        $res->errcode = 0;
        try{
            foreach($request->all() as $key => $value){
                if (in_array($key, Setting::ALLOWED)){
                    Setting::where('key', $key)->update(['value' => $value]);
                }
            }
            broadcast(new GlobalSettingsChanged());
        } catch (Exception $e) {
            $res->errcode = 1;
            $res->errmess = $e->getMessage();
            Log::error($e);
        }
        return response()->json($res);
    }
}