<?php

namespace App\Console\Commands;

use App\Call;
use App\User;
use App\Module;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Mail\SubscriptionExpiracy;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewMissedCall;
use Illuminate\Support\Facades\Mail;

class InformAboutSubscriptionExpiracy extends Command
{
    protected $signature = 'inform:subscription';

    protected $description = 'Checking current user subscriptions and sending inform emails';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();
        $modules = Module::where('enabled', '=', '1')->get();
        $users = User::where('is_admin', true)->get();

        if ($modules->count() == 0)
        {
            return;
        }

        $mod = $modules[0];
        if (isset($mod->current_subscription_date_start))
        {
            $diff_in_days = $now->diffInDays(Carbon::parse($mod->current_subscription_date_end));

            if ($diff_in_days == 30 || $diff_in_days == 14 || $diff_in_days == 7 || $diff_in_days == 3 || $diff_in_days == 1)
            {
                foreach($users as $user)
                {
                    Mail::to($user)->send(new SubscriptionExpiracy($modules, $diff_in_days, $user));
                }
            }
        }        
    }
}
