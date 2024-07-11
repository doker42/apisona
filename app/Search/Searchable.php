<?php

namespace App\Search;

use App\Observers\ElasticsearchObserver;
use Elastic\Elasticsearch\Client;

trait Searchable
{
    public static function bootSearchable(): void
    {
        if (config('services.search.enabled')) {
            static::observe(ElasticsearchObserver::class);
        }
    }

    public function elsIndex(Client $elsClient)
    {
        return  $elsClient->index([
            'index' => $this->getIndex(),
            'type'  => $this->getType(),
            'id'    => $this->id,
            'body'  => $this->toBodyArray(),
        ]);
    }

    public function elsDelete(Client $elsClient)
    {
        $elsClient->delete([
            'index' => $this->getIndex(),
            'type'  => $this->getType(),
            'id'    => $this->id,
        ]);
    }

    abstract public function toBodyArray(): array;
}
