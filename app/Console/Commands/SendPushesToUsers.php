<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use Illuminate\Console\Command;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendPushesToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:pushes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks every hour if there are workers with work schedule and devices and sends pushes through firebase';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::with('user_devices')->get();


        $serviceAccount = ServiceAccount::fromValue(Storage::get('diga-team-time-tracker-firebase-adminsdk-m4m8m-d95f7f726e.json'));
        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->createMessaging();

        foreach($users as $user){
            if (count($user->user_devices) > 0){
                $tz = env('APP_TIMEZONE');
                $working_days = json_decode($user->working_days);
                $current_day_of_week = Carbon::now($tz)->dayOfWeekIso;

                if (in_array($current_day_of_week, $working_days)){
                    $now = Carbon::now($tz);

                    $start = Carbon::createFromTimeString($user->day_start_time, $tz);
                    $lunch = Carbon::createFromTimeString($user->lunch_time, $tz);
                    $lunch_end = Carbon::createFromTimeString($user->lunch_time, $tz)->addHour();
                    $end = Carbon::createFromTimeString($user->day_finish_time, $tz);

                    $send_message = false;
                    $title = "Timetracker";
                    $message = $user->name . ', ';

                    if ($now->diffInMinutes($start, false) == 0){
                        $message = $message . trans('template.invoice_canceled');
                        $send_message = true;
                    }
                
                    if ($now->diffInMinutes($lunch, false) == 0){
                        $message = $message . trans('template.lunch_message');
                        $send_message = true;
                    }

                    if ($now->diffInMinutes($lunch_end, false) == 0){
                        $message = $message . trans('template.from_lunch_message');
                        $send_message = true;
                    }

                    if ($now->diffInMinutes($end, false) == 0){
                        $message = $message . trans('template.end_message');
                        $send_message = true;
                    }

                    $data = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'avatar' => $user->photo
                    ];
    
                    $notification = Notification::fromArray([
                        'title' => $title,
                        'body' => $message,
                        'data' => $data
                    ]);

                    if ($send_message === true){
                        foreach($user->user_devices as $user_device){
    
                            $message = CloudMessage::withTarget('token', $user_device->token)
                                ->withNotification($notification);
                            $firebase->send($message);
        
                        }
                    }  
                }
            }
        }
    }
}
