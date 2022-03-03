<?php

namespace Rkesa\Planning\Notifications;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Rkesa\Project\Models\Project;
use Rkesa\Service\Models\Service;
use Sabre\VObject\Property\Integer;

class PaymentStep extends Notification
{
    use Queueable;

    public $estimate_number;
    public $estimate_id;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($estimate_number, $estimate_id)
    {
        $this->estimate_number = $estimate_number;
        $this->estimate_id = $estimate_id;
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
            'left_part' => trans('template.Accountant_reminder'),
            'estimate_number' => $this->estimate_number,
            'estimate_id' => $this->estimate_id,
            'created_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'estimate_number' => $this->estimate_number,
            'estimate_id' => $this->estimate_id,
        ];
    }

    public static function fromDatabase($not)
    {
        $not->left_part = trans('template.Accountant_reminder');
        $not->estimate_number = $not->data['estimate_number'];
        $not->estimate_id = $not->data['estimate_id'];
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
