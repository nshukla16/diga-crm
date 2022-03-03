<?php

namespace Rkesa\Project\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProjectApplicationsAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public $project_id;
    public $project_name;
    // public $user_name;
    // public $user_id;
    public $manufacturer_id;
    public $manufacturer_name;
    public $not_type;

    public function __construct(/*$user, */$not_type, $project_id, $project_name, $manufacturer_id, $manufacturer_name)
    {
        // $this->user_name = $user['name'];
        // $this->user_id = $user['id'];
        $this->not_type = $not_type;
        $this->project_id = $project_id;
        $this->project_name = $project_name;
        $this->manufacturer_id = $manufacturer_id;
        $this->manufacturer_name = $manufacturer_name;
    }

    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            // 'user_id' => $this->user_id,
            // 'user_name' => $this->user_name,
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'manufacturer_id' => $this->manufacturer_id,
            'manufacturer_name' => $this->manufacturer_name,
            'not_type' => $this->not_type,
            'created_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            // 'user_id' => $this->user_id,
            // 'user_name' => $this->user_name,
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'manufacturer_id' => $this->manufacturer_id,
            'manufacturer_name' => $this->manufacturer_name,
            'not_type' => $this->not_type,
        ];
    }

    public static function fromDatabase($not)
    {
        // $not->user_id = $not->data['user_id'];
        // $not->user_name = $not->data['user_name'];
        $not->project_id = $not->data['project_id'];
        $not->project_name = $not->data['project_name'];
        $not->manufacturer_id = $not->data['manufacturer_id'];
        $not->manufacturer_name = $not->data['manufacturer_name'];
        $not->not_type = $not->data['not_type'];

        return $not;
    }
}
