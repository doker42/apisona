<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Traits\UserTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;

/**
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $phone
 * @property string $timezone
 * @property string $avatar
 */

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserTrait;

    public const PASSPORT_CLIENT_NAME = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'last_active',
        'last_active_ip',
        'email_verify_token',
        'email_verified_at',
        'on_boarding_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
