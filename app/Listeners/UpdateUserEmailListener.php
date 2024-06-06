<?php

namespace App\Listeners;

use App\Mail\ChangeUserEmailMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class UpdateUserEmailListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $user = $event->user;
        $emailSet = $event->emailSet;
        Mail::toWithBcc($emailSet->email)->queue(new ChangeUserEmailMail($user, $emailSet->token));
    }
}
