<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionExpiracy extends Mailable
{
    use Queueable, SerializesModels;

    public $modules;
    public $number_of_days;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($modules, $number_of_days, $user)
    {
        $this->modules = $modules;
        $this->number_of_days = $number_of_days;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {        
        return $this->
            // from('geral@diga.pt')->
            subject(trans('template.SubscriptionExpiracy', [], $this->user->site_language))->
            view('emails.subscription_expiracy');
    }
}
