<?php

namespace App\Providers;

use App\Helpers\Mail as MailHelp;
use App\Search\Article\ElasticsearchRepository;
use App\Search\Article\EloquentSearchRepository;
use App\Search\SearchRepository;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SearchRepository::class, function () {
            // This is useful in case we want to turn-off our
            // search cluster or when deploying the search
            // to a live, running application at first.
            if (! config('services.search.enabled')) {
                return new EloquentSearchRepository();
            }

            return new ElasticsearchRepository(
                $this->app->make(Client::class)
            );
        });

        $this->bindSearchClient();
    }


    private function bindSearchClient()
    {
        $this->app->bind(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts($app['config']->get('services.search.hosts'))
                ->setBasicAuthentication(env('ELASTIC_BA_USERNAME', 'elastic'), env('ELASTIC_BA_PASSWORD', 'password'))
                ->build();
        });
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
