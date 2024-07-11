<?php

namespace App\Models;


use App\Search\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    use Searchable;

    public const ARTICLE_INDEX = 'article';
    public const ARTICLE_TYPE = '_doc';

    protected $fillable = [
        'title',
        'body',
        'tags'
    ];

    protected $casts = [
        'tags' => 'json',
    ];


    public function getIndex()
    {
        return self::ARTICLE_INDEX;
    }


    public function getType()
    {
        return self::ARTICLE_TYPE;
    }

    public function toBodyArray(): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'tags'  => $this->tags
        ];
    }
}
