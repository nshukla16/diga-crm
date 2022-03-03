<?php

namespace App\Console\Commands;

use App\Call;
use App\User;
use App\Module;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Notifications\NewMissedCall;

class CheckSubscription extends Command
{
    protected $signature = 'check:subscription';

    protected $description = 'Checking current user subscriptions';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();
        $modules = Module::allowed();

        foreach($modules as $mod)
        {
            if (isset($mod->current_subscription_date_start))
            {
                if (Carbon::parse($mod->current_subscription_date_end)->lt($now))
                {
                    $mod->enabled = 0;
                    $mod->save();
                }
            }
            else{
                if (isset($mod->trial_date_start))
                {
                    if (Carbon::parse($mod->trial_date_end)->lt($now))
                    {
                        $mod->enabled = 0;
                        $mod->save();
                    }
                }
            }
        }
    }
}
