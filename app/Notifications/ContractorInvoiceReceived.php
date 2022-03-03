<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Rkesa\Service\Models\Service;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ContractorInvoiceReceived extends Notification
{
    use Queueable;

    public $service;
    public $invoice_file;
    public $invoice_file_name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Service $service, $invoice_file, $invoice_file_name)
    {
        $this->service = $service;
        $this->invoice_file = $invoice_file;
        $this->invoice_file_name = $invoice_file_name;
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
            'service_number' => $this->service->get_service_number() . ' ' . $this->service->name,
            'service_id' => $this->service->id,
            'contact_name' => $this->service->client_contact->name . ' ' . $this->service->client_contact->surname,
            'contact_id' => $this->service->client_contact->id,
            'created_at' => Carbon::now()->toDateTimeString(),
            'invoice_file' => $this->invoice_file,
            'invoice_file_name' => $this->invoice_file_name,
        ]);
    }

    public static function fromDatabase($not)
    {
        $service = Service::find($not->data['service_id']);

        $not->service_number = $service->get_service_number() . ' ' . $service->name;
        $not->service_id = $service->id;
        $not->contact_name = $service->client_contact->name . ' ' . $service->client_contact->surname;
        $not->contact_id = $service->client_contact->id;
        $not->invoice_file = $not->data['invoice_file'];
        $not->invoice_file_name = $not->data['invoice_file_name'];
        return $not;
    }

    public function toDatabase($notifiable)
    {
        return [
            'service_id' => $this->service->id,
            'invoice_file' => $this->invoice_file,
            'invoice_file_name' => $this->invoice_file_name
        ];
    }
}
