<?php

namespace App\Console;

use App\Call;
use App\Notifications\NewMissedCall;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Setting;
use Rkesa\Estimate\Models\EstimatePayStage;
use Rkesa\Planning\Notifications\PaymentStep;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
//        'App\Console\Commands\InformAccountant',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
          $schedule->command('inform:accountant')->everyMinute();
          $schedule->command('check:subscription')->daily();
          $schedule->command('inform:subscription')->daily();
          $schedule->command('move:events')->daily();
          $schedule->command('send:paystages')->daily();
          $schedule->command('send:pushes')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
