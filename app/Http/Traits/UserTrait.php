<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;

trait UserTrait
{
    /**
     * @param string $email
     * @return void
     */
    public static function removeInviteToken(string $email): void
    {
        DB::table('password_reset_tokens')->where(['email'=> $email])->delete();
    }


    /**
     * @param string $date
     * @param bool $person
     * @return bool
     */
    public static function expired(string $date, bool $person=false): bool
    {
        $created_unx = strtotime($date);
        $expired_time = config('auth.invite_expired');
        $time = $created_unx + $expired_time;

        return time() > $time;
    }
}
