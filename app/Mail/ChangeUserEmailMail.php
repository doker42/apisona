<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class ChangeUserEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    protected object $user;

    protected string $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(object $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $locale = $this->user->language ?: config('app.locale');
        App::setLocale($locale);

        $data = [
            'greeting'       => __('Hi :user_name', ['user_name' => $this->user->name]),
            'message_first'  => __('To verify your new email, please click the button below:'),
            'button' => [
                'text' => __('Verify email'),
                'url'  => 'url' //todo
            ],
            'message_second' => __('If the button is not displayed, please click'),
            'here'           => __('here'),
            'message_three'  => __('Got this message by mistake? Please just ignore it.'),
            'support_mail'   => config('mail.support_mail'),
        ];

        return $this->view('email.mail-basic', $data)->subject(__('Please verify your new email'));
    }
}
