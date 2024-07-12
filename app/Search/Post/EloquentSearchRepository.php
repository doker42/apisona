<?php

namespace App\Search\Post;

use App\Models\Post;
use App\Search\SearchRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class EloquentSearchRepository implements SearchRepository
{
    public function search(string $term): Collection
    {
        $items = Post::query()
            ->where(fn ($query) => (
            $query->where('content', 'LIKE', "%{$term}%")
                ->orWhere('title', 'LIKE', "%{$term}%")
            ))
            ->get();

        return $this->buildCollection($items);
    }


    private function buildCollection($items): Collection
    {
        $ids = Arr::pluck($items, 'id');

        return Post::findMany($ids)
            ->sortBy(function ($post) use ($ids) {
                return array_search($post->getKey(), $ids);
            });
    }
}
