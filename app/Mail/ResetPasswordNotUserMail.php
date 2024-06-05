<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordNotUserMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'greeting'       => __('This email not found'),
            'message_first'  => __('User with this email was not found on our platform, please try another email address.'),
            'message_three'  => __('Ignore this message if you didn’t request to change your password.'),
            'support_mail'   => config('mail.support_mail'),
        ];

        return $this->view('email.mail-basic', $data)->subject(__('Trying to reset your password?'));
    }
}
