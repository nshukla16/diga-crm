<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rkesa\Calendar\Models\Event;

class InsertIntoEventsUrlFromDescription extends Migration
{
    public function up()
    {
        $events = Event::all();
        foreach($events as $event)
        {
            if (strpos($event->description, 'https://gofrotech.diga.pt/projects/') !== false)
            {
                $event->url = $event->description;
                $event->description = NULL;
                $event->save();
            }
        }
    }

    public function down()
    {
        $events = Event::all();
        foreach($events as $event)
        {
            if (strpos($event->url, 'https://gofrotech.diga.pt/projects/') !== false)
            {
                $event->description = $event->url;
                $event->url = NULL;
                $event->save();
            }
        }
    }
}
