<?php

namespace App\Search\Article;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class EloquentSearchRepository implements SearchRepository
{
    public function search(string $term): Collection
    {
        $items = Article::query()
            ->where(fn ($query) => (
            $query->where('body', 'LIKE', "%{$term}%")
                ->orWhere('title', 'LIKE', "%{$term}%")
            ))
            ->get();

        return $this->buildCollection($items);
    }


    private function buildCollection($items): Collection
    {
        $ids = Arr::pluck($items, 'id');

        return Article::findMany($ids)
            ->sortBy(function ($article) use ($ids) {
                return array_search($article->getKey(), $ids);
            });
    }
}
