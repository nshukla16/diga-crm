<?php

namespace App\Notifications;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Rkesa\Calendar\Models\Event;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $event_id;
    public $event_type_id;
    public $contact_name;
    public $client_contact_id;
    public $company_name;
    public $company_id;
    //
    public $user_name;
    public $user_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $event, array $user)
    {
        $this->event_id = $event['id'];
        $this->event_type_id = $event['event_type_id'];
        if ($event['client_contact']){
            $this->contact_name = $event['client_contact']['name'].' '.$event['client_contact']['surname'];
            $this->client_contact_id = $event['client_contact_id'];
            $this->company_name = $event['client_contact']['client'] ? $event['client_contact']['client']['name'] : '';
            $this->company_id = $event['client_contact']['client'] ? $event['client_contact']['client_id'] : '';
        }

        //
        $this->user_name = $user['name'];
        $this->user_id = $user['id'];        
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
            'initiator_name' => $this->user_name,
            'event_type_id' => $this->event_type_id,
            'contact_name' => $this->contact_name,
            'contact_id' => $this->client_contact_id,
            'company_name' => $this->company_name,
            'company_id' => $this->company_id,
            'created_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'event_id' => $this->event_id,
            'event_type_id' => $this->event_type_id,
            'initiator_user_id' => $this->user_id
        ];
    }

    public static function fromDatabase($not)
    {
        $event = Event::find($not->data['event_id']);
        $initiator = User::withTrashed()->find($not->data['initiator_user_id']);
        $not->initiator_name = $initiator->name;
        $not->event_type_id = $not->data['event_type_id'];
        $not->contact_name = $event->client_contact->name.' '.$event->client_contact->surname;
        $not->contact_id = $event->client_contact->id;
        $not->company_name = $event->client_contact->client ? $event->client_contact->client->name : '';
        $not->company_id = $event->client_contact->client ? $event->client_contact->client->id : '';
        $not->project_id = $event->project_id;
        $not->project_name = $event->project ? $event->project->name : '';
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
//            $this->event->id
//        ];
//    }
}
