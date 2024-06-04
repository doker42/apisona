<?php

namespace App\Helpers;

class Mail
{

    public const BCC_DEFAULT = 'default';

    public static function bccTo(string $bcc)
    {
        return config('mail.bcc.' . $bcc);
    }
}
