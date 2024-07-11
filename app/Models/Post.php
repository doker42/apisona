<?php

namespace App\Models;

use App\Search\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    use Searchable;

    public const POST_INDEX = 'post';
    public const POST_TYPE  = '_doc';

    protected $fillable = [
        'title',
        'content',
        'user_id'
    ];

    public function getIndex()
    {
        return self::POST_INDEX;
    }


    public function getType()
    {
        return self::POST_TYPE;
    }

    public function toBodyArray(): array
    {
        return [
            'title'    => $this->title,
            'content'  => $this->content,
        ];
    }

}
