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
use Rkesa\Client\Models\ClientContact;

class ContactAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $contact;
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ClientContact $contact, User $user)
    {
        $this->contact = $contact;
        $this->user = $user;
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
            'left_part' => trans('template.Contact_assigned_notification', ['contact' => $this->contact->name_with_link]),
            'right_part' => trans('template.User_changed_contact', ['initiator' => $this->user->name]),
            'created_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'contact_id' => $this->contact->id,
            'initiator_user_id' => $this->user->id,
        ];
    }

    public static function fromDatabase($not)
    {
        $contact = ClientContact::find($not->data['contact_id']);
        $initiator = User::withTrashed()->find($not->data['initiator_user_id']);
        $not->left_part = trans('template.Contact_assigned_notification', ['contact' => $contact->name_with_link]);
        $not->right_part = trans('template.User_changed_contact', ['initiator' => $initiator->name]);
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
