<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
        $request = $this->app->make('request');

        $this->registerPolicies();

//        dd($request->except('_token'));
//        dd($request->path());

        $refreshTokenLifetime = config('passport.tokens_lifetime.refresh_token');
        if ($request->path() == 'oauth/token' && $request->has('remember') && $request->get('remember')) {
            $refreshTokenLifetime = config('passport.tokens_lifetime.refresh_token_remember_me');
        }

//        dd($refreshTokenLifetime);

        Passport::enablePasswordGrant();

        Passport::tokensExpireIn(now()->addMinutes(config('passport.tokens_lifetime.token')));
        Passport::refreshTokensExpireIn(now()->addMinutes($refreshTokenLifetime));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(config('passport.tokens_lifetime.personal_access_token')));
    }
}
