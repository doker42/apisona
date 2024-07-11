<?php

namespace App\Observers;

use Elastic\Elasticsearch\Client;

class ElasticsearchObserver
{
    public function __construct(private Client $elasticsearchClient)
    {
        $this->elasticsearch = $elasticsearchClient;
    }

    public function saved($model)
    {
        $model->elsIndex($this->elasticsearchClient);
    }

    public function deleted($model)
    {
        $model->elsDelete($this->elasticsearchClient);
    }
}
