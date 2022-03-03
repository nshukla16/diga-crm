<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\GlobalSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Rkesa\Calendar\Models\Event;

class MoveEventsToNextDay extends Command
{
    protected $signature = 'move:events';

    protected $description = 'If event on current day is not completed it moves it on the next day';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $gs = GlobalSettings::first();
        if ($gs->move_events_to_next_day)
        {            
            $events = Event::whereDate('start', Carbon::today())->where('done', '0')->get();

            foreach($events as $event)
            {
                if (isset($gs->move_events_to_next_day_time))
                {
                    $dt = Carbon::parse($event->start)->addDay()->toDateString();

                    $event->start = Carbon::parse($dt.' '.$gs->move_events_to_next_day_time);
                }
                else
                {
                    $event->start = Carbon::parse($event->start)->addDay();
                }
                $event->save();
            }
        }
    }
}
