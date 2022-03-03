<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewChatMessages extends Mailable
{
    use Queueable, SerializesModels;

    public $service_referrer;
    public $user_name_from;
    public $locale;
    public $token;


    public function __construct($service_referrer, $user_name_from, $locale, $token)
    {
        $this->service_referrer = $service_referrer;
        $this->user_name_from = $user_name_from;
        $this->locale = $locale;
        $this->token = $token;
    }

    public function build()
    {
        return $this->
            subject(trans('template.New_messages_in_chat', [], $this->locale))->
            view('emails.new_chat_messages');

    }
}
