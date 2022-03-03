<?php

namespace App\Notifications;

use App\Connection;
use Illuminate\Bus\Queueable;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewConnection extends Notification implements ShouldQueue
{
    use Queueable;

    public $conn;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
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
            'left_part' => trans('template.new_connection_notification', ['connection' => $this->conn->url]),
            'right_part' => trans('template.confirm_or_ignore'),
            'created_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'connection_id' => $this->conn->id
        ];
    }

    public static function fromDatabase($not)
    {
        $connection = Connection::find($not->data['connection_id']);
        $not->left_part = trans('template.new_connection_notification', ['connection' => $connection->url]);
        $not->right_part = trans('template.confirm_or_ignore');
        return $not;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // public function toArray($notifiable)
    // {
    //     return [
    //         //
    //     ];
    // }
}
