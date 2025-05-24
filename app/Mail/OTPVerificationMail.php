<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $name;
    public $url;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $name, $url)
    {
        $this->otp = $otp;
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * Build the message.
     */
    public function build()
    {
return $this->subject('Password Reset OTP')
            ->view('emails.otp')
            ->with([ 
                'otp' => $this->otp, 
                'name' => $this->name,
                'url' => $this->url 
            ]);
    }
}
