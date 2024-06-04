<?php

namespace App\Providers;

use App\Helpers\Mail as MailHelp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** Customize corporate BCC */
        Mail::macro('toWithBcc', function (mixed $users, string $bcc = null) {

            $bcc = $bcc
                ? MailHelp::bccTo($bcc)
                : MailHelp::bccTo(MailHelp::BCC_DEFAULT);

            if (env('APP_ENV') != 'testing') {
                return  Mail::to($users)->bcc($bcc);
            } else {
                return Mail::fake();
            }
        });
    }
}
