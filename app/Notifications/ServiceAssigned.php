<?php

namespace App\Notifications;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Rkesa\Service\Models\Service;

class ServiceAssigned extends Notification
{
    use Queueable;

    public $service;
    public $user_name;
    public $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Service $service, User $user)
    {
        $this->service = $service;
        $this->user_name = $user->name;
        $this->user_id = $user->id;
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
            'service_number' => $this->service->get_service_number().' '.$this->service->name,
            'service_id' => $this->service->id,
            'contact_name' => $this->service->client_contact->name.' '.$this->service->client_contact->surname,
            'contact_id' => $this->service->client_contact->id,
            'company_name' => $this->service->client_contact->client ? $this->service->client_contact->client->name : '',
            'company_id' => $this->service->client_contact->client ? $this->service->client_contact->client->id : '',
            'created_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    public static function fromDatabase($not)
    {
        $service = Service::find($not->data['service_id']);
        $initiator = User::withTrashed()->find($not->data['initiator_user_id']);
        $not->initiator_name = $initiator->name;
        $not->service_number = $service->get_service_number().' '.$service->name;
        $not->service_id = $service->id;
        $not->contact_name = $service->client_contact->name.' '.$service->client_contact->surname;
        $not->contact_id = $service->client_contact->id;
        $not->company_name = $service->client_contact->client ? $service->client_contact->client->name : '';
        $not->company_id = $service->client_contact->client ? $service->client_contact->client->id : '';
        return $not;
    }

    public function toDatabase($notifiable)
    {
        return [
            'service_id' => $this->service->id,
            'initiator_user_id' => $this->user_id
        ];
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
