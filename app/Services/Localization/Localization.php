<?php

namespace App\Services\Localization;


class Localization
{
    public function locale()
    {
        $locale = request()->segment(2, '');

        return ($locale && in_array($locale, config("app.locales"))) ? $locale : "";
    }
}
