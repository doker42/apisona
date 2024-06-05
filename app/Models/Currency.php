<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'symbol'
    ];

    protected $table = 'currencies';

    public function subscriptions(): object
    {
        return $this->hasMany(Subscription::class);
    }

    public function plans(): object
    {
        return $this->hasMany(Plan::class);
    }
}
