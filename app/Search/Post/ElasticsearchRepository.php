<?php

namespace App\Search\Post;

use App\Models\Article;
use App\Models\Post;
use App\Search\SearchRepository;
use Elastic\Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class ElasticsearchRepository implements SearchRepository
{
    /** @var \Elastic\Elasticsearch\Client */
    private $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }


    /**
     * @param string $query
     * @return Collection
     */
    public function search(string $query = ''): Collection
    {
        $items = $this->searchOnElasticsearch($query);

        return $this->buildCollection($items);
    }

    private function searchOnElasticsearch(string $query = ''): array
    {
        $model = new Post;

        $items = $this->elasticsearch->search([
            'index' => $model->getIndex(),
            'type'  => $model->getType(),
            'body' => [
                'query' => [
                    'multi_match' => [
                        'fields'  => ['title^5', 'content'],
                        'query'   => $query,
                    ],
                ],
            ],
        ]);

        return $items->asArray();
    }


    private function buildCollection(array $items): Collection
    {
        $ids = Arr::pluck($items['hits']['hits'], '_id');

        return Post::findMany($ids)
            ->sortBy(function ($article) use ($ids) {
                return array_search($article->getKey(), $ids);
            });
    }

}
