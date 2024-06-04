<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class EmailSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'entity_id',
        'entity_type',
    ];


    /**
     * @return MorphTo
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }


    /**
     * @param User $entity
     * @param $email
     * @return mixed
     */
    public static function addRequest(User $entity, $email): mixed
    {
        $data = [
            'token'       => Str::random(60),
            'email'       => $email,
            'entity_id'   => $entity->id,
            'entity_type' => $entity::class,
        ];

        $sets = self::where([
            'entity_id'   => $data['entity_id'],
            'entity_type' => $data['entity_type']
        ])->first();


        if(!$sets){
            return self::create($data);
        }
        if ($sets->email !== $email) {
            $sets->update($data);
        } else {
            if (!$sets->isExpired()) {
                return false;
            } else {
                $sets->update($data);
            }
        }

        return $sets;
    }


    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        $time = strtotime($this->updated_at) + config('auth.email_request_expired');
        return time() > $time;
    }
}
