<?php

use App\Chat;
use App\Role;
use App\User;
use App\Module;
use App\Tariff;
use App\Setting;
use Carbon\Carbon;
use App\ChatMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TariffAndModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($lang = 'ru', $email, $password, $token, $numberOfUsers = 0, $tariff='base', $trialDays = 7, $days = 0, $modules = '')
    {
        $modules_list = explode(',', $modules);

        $tariff_d = Tariff::with('modules')->where('name', $tariff)->first();
        if ($tariff_d == null){
            $tariff_d = Tariff::with('modules')->where('name', 'base')->first();
        }

        $modules_d = Module::all();
        foreach($modules_d as $m){
            $m->enabled = false;
            foreach($tariff_d->modules as $m_t){
                if ($m->id == $m_t->id){
                    $m->enabled = true;
                    if ($trialDays > 0)
                    {
                        $m->trial_date_start = Carbon::now();
                        $m->trial_date_end = Carbon::now()->addDays($trialDays);
                    }
                    if ($days > 0){
                        $m->current_subscription_date_start = Carbon::now();
                        $m->current_subscription_date_end = Carbon::now()->addDays($days);
                    }
                }
            }
            foreach($modules_list as $m_name){
                if ($m->name == $m_name){
                    $m->enabled = true;
                    if ($trialDays > 0)
                    {
                        $m->trial_date_start = Carbon::now();
                        $m->trial_date_end = Carbon::now()->addDays($trialDays);
                    }
                    if ($days > 0){
                        $m->current_subscription_date_start = Carbon::now();
                        $m->current_subscription_date_end = Carbon::now()->addDays($days);
                    }                    
                }
            }

            $m->save();
        }

        if ($trialDays > 0)
        {
            $tariff_d->trial_date_start = Carbon::now();
            $tariff_d->trial_date_end = Carbon::now()->addDays($trialDays);
        }
        if ($days > 0){
            $tariff_d->current_subscription_date_start = Carbon::now();
            $tariff_d->current_subscription_date_end = Carbon::now()->addDays($days);
        }

        $setting_max_users =  Setting::where('key', '=', 'max_users')->first();
        $setting_max_users->value = $numberOfUsers;
        $setting_max_users->save();
    }
}