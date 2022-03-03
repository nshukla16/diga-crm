<?php

namespace App\Http\Controllers;

use App\Module;
use App\Tariff;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        // $fields = $request->input('fields', '*');
        // $fields_array = explode(",", $fields);
        // return Module::select($fields_array)->get();
        return Module::allowed();
    }

    public static function receive_from_saas($pm_data, $pg_data)
    {
        $pms = json_decode($pm_data);
        $pg = json_decode($pg_data);

        $setting_currency = Setting::where('key', '=', 'price_currency')->firstOrFail();
        $setting_currency->value = $pg->currency;
        $setting_currency->save();

        $setting_ppu = Setting::where('key', '=', 'price_per_user')->firstOrFail();
        $setting_ppu->value = $pg->price_per_user;
        $setting_ppu->save();


        $modules = Module::allowed();
        foreach($modules as $mod)
        {
            foreach($pms as $pm)
            {
                if ($mod->name == $pm->module->name){
                    $mod->price = $pm->price;
                    $mod->save();
                    break;
                }
            }
        }
    }

    public static function update_tariffs_and_modules($modules_data, $number_of_users)
    {
        $modules_new = json_decode($modules_data);

        $modules = Module::get();

        foreach($modules as $mod)
        {
            $mod->enabled = false;
            $mod->price = 0;
            $mod->current_subscription_date_start = null;
            $mod->current_subscription_date_end = null;
            $mod->trial_date_start = null;
            $mod->trial_date_end = null;

            foreach($modules_new as $pm)
            {
                if ($mod->name == $pm->name){
                    $mod->current_subscription_date_start = $pm->current_subscription_date_start;
                    $mod->current_subscription_date_end = $pm->current_subscription_date_end;
                    $mod->trial_date_start = $pm->trial_date_start;
                    $mod->trial_date_end = $pm->trial_date_end;

                    if ($mod->trial_date_end != null && Carbon::parse($mod->trial_date_end)->gt(Carbon::now())){
                        $mod->enabled = true;
                    }
                    if ($mod->current_subscription_date_end != null && Carbon::parse($mod->current_subscription_date_end)->gt(Carbon::now())){
                        $mod->enabled = true;
                    }
                    break;
                }
            }

            $mod->save();
        }

        $setting_max_users =  Setting::where('key', '=', 'max_users')->first();
        $setting_max_users->value = $number_of_users;
        $setting_max_users->save();
    }
}
