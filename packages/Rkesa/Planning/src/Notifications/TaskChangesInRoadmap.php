<?php

namespace Rkesa\Planning\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskChangesInRoadmap extends Notification
{
    use Queueable;

    public $estimate_id;
    public $estimate_name;
    public $roadmap_id;
    public $roadmap_name;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($estimate_id,$estimate_name,$roadmap_id,$roadmap_name)
    {
        $this->estimate_id = $estimate_id;
        $this->estimate_name = $estimate_name;
        $this->roadmap_id = $roadmap_id;
        $this->roadmap_name = $roadmap_name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
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
            'left_part' => trans('template.In_roadmap'),
            'right_part' => trans('template.Task_has_been_changed'),
            'estimate_number' => $this->estimate_name,
            'estimate_id' => $this->estimate_id,
            'roadmap_id' => $this->roadmap_id,
            'roadmap_name' => $this->roadmap_name,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
//            'estimate_number' => $this->estimate_number,
//            'estimate_id' => $this->estimate_id,
//            'estimate_number' => $this->service,

            'estimate_number' => $this->estimate_name,
            'estimate_id' => $this->estimate_id,
            'roadmap_id' => $this->roadmap_id,
            'roadmap_name' => $this->roadmap_name,
        ];
    }

    public static function fromDatabase($not)
    {
        $not->left_part = trans('template.In_roadmap');
        $not->right_part = trans('template.Task_has_been_changed');
//        $not->estimate_number = $not->data['estimate_number'];
        $not->estimate_id = $not->data['estimate_id'];
        $not->estimate_number = $not->data['estimate_number'];
        $not->roadmap_id = $not->data['roadmap_id'];
        $not->roadmap_name = $not->data['roadmap_name'];
        return $not;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
//    public function toArray($notifiable)
//    {
//        return [
//            //
//        ];
//    }
}
