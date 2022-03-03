<?php

namespace Rkesa\Project\Notifications;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Rkesa\Calendar\Models\Event;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AutoTaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $event_id;
    public $event_type_id;
    public $contact_name;
    public $client_contact_id;
    public $company_name;
    public $company_id;
    public $event_description;
    public $event_url;
    public $project_id;
    public $project_name;
    //
    public $user_name;
    public $user_id;
    public $manufacturer_id;
    public $manufacturer_name;
    public $not_type;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $event, array $user, $not_type, $manufacturer_id, $manufacturer_name, $project_name)
    {
        $this->event_id = $event['id'];
        $this->event_type_id = $event['event_type_id'];
        $this->contact_name = $event['client_contact']['name'].' '.$event['client_contact']['surname'];
        $this->client_contact_id = $event['client_contact_id'];
        $this->company_name = $event['client_contact']['client'] ? $event['client_contact']['client']['name'] : '';
        $this->company_id = $event['client_contact']['client'] ? $event['client_contact']['client_id'] : '';
        $this->manufacturer_id = $manufacturer_id;
        $this->manufacturer_name = $manufacturer_name;
        $this->event_description = $event["description"];
        $this->event_url = $event["url"];
        $this->project_id = $event["project_id"];
        $this->project_name = $project_name;
        //
        $this->user_name = $user['name'];
        $this->user_id = $user['id'];
        $this->not_type = $not_type;
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
            'manufacturer_id' => $this->manufacturer_id,
            'manufacturer_name' => $this->manufacturer_name,
            'not_type' => $this->not_type,
            'created_at' => Carbon::now()->toDateTimeString(),
            'event_description' => $this->event_description,
            'event_url' => $this->event_url,
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'event_id' => $this->event_id,
            'event_type_id' => $this->event_type_id,
            'initiator_user_id' => $this->user_id,
            'not_type' => $this->not_type,
            'manufacturer_id' => $this->manufacturer_id,
            'manufacturer_name' => $this->manufacturer_name,
            'project_name' => $this->project_name,
        ];
    }

    public static function fromDatabase($not)
    {
        $event = Event::find($not->data['event_id']);
        $initiator = User::withTrashed()->find($not->data['initiator_user_id']);
        $not->initiator_name = $initiator->name;
        $not->event_type_id = $not->data['event_type_id'];
        $not->not_type = $not->data['not_type'];
        $not->manufacturer_id = $not->data['manufacturer_id'];
        $not->manufacturer_name = $not->data['manufacturer_name'];
        $not->contact_name = $event->client_contact->name.' '.$event->client_contact->surname;
        $not->contact_id = $event->client_contact->id;
        $not->company_name = $event->client_contact->client ? $event->client_contact->client->name : '';
        $not->company_id = $event->client_contact->client ? $event->client_contact->client->id : '';
        $not->project_id = $event->project_id;
        $not->project_name = $event->project ? $event->project->name : '';
        $not->event_description = $event->description;
        $not->event_url = $event->url;
        return $not;
    }

}
