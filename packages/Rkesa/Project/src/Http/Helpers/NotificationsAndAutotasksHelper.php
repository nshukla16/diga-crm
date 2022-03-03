<?php

namespace Rkesa\Project\Http\Helpers;

use Rkesa\Calendar\Models\Event;
use Rkesa\Project\Models\Project;
use Illuminate\Support\Facades\Auth;
use Rkesa\Project\Models\Manufacturer;
use Rkesa\Project\Models\ProjectAutotask;
use Rkesa\Project\Models\ProjectNotification;
use Rkesa\Project\Notifications\ProjectChanged;
use Rkesa\Project\Notifications\AutoTaskAssigned;
use Rkesa\Calendar\Http\Controllers\CalendarController;
use Log;

class NotificationsAndAutotasksHelper{
    public static function send_notifications($project_id, $not_type, $current_user, $notif_obj = null)
    {
        $project = Project::find($project_id);
        $notif_obj = $notif_obj ?: new ProjectChanged($project, $current_user, $not_type);
        $not = ProjectNotification::where('type', $not_type)->first();
        foreach($not->recipients as $r){
            switch($r->type){
                case 1:
                    foreach($r->group->users as $user){
                        $user->notify($notif_obj);
                    }
                    break;
                case 2:
                    $r->group->head_user->notify($notif_obj);
                    break;
                case 3:
                    $project->responsible_user->notify($notif_obj);
                    break;
                case 4:
                    $r->user->notify($notif_obj);
                    break;
            }
        }
    }

    public static function create_autotask($project, $not_type, $custom_start = null, $manufacturer_id = null, $description = null, $url = null)
    {
        $user = Auth::user();
        $autotask = ProjectAutotask::where('type', $not_type)->first();
        foreach($autotask->recipients as $recipient) {
            $e = new Event;
            $e->creator_user_id = Auth::user()->id;
            switch ($recipient->type){
                case 2:
                    $e->user_id = $recipient->group->head_user_id;
                    break;
                case 3:
                    $e->user_id = $project->responsible_user_id;
                    break;
                case 4:
                    $e->user_id = $recipient->user_id;
                    break;
            }
            $e->start = $custom_start == null ? CalendarController::get_date_from_type($recipient->event_date) : $custom_start;
            $e->client_contact_id = $project->client->client_contacts->first()->id;
            $e->event_type_id = $recipient->event_type_id;
            $e->project_id = $project->id;
            $e->done = false;
            $e->description = $description;
            $e->url = $url;
            $e->save();

            $manufacturer_name = null;
            if ($manufacturer_id){
                $manufacturer_name = Manufacturer::find($manufacturer_id)->name;
            }

            $e->load('client_contact.client');
            $e->user->notify(new AutoTaskAssigned($e->toArray(), $user->toArray(), $not_type, $manufacturer_id, $manufacturer_name, $project->name));
        }
    }
}
