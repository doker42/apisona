<?php
/**
 * Created by PhpStorm.
 * User: Viktor
 * Date: 30.09.2019
 * Time: 23:14
 */

namespace App\Services\Localization;

use Illuminate\Support\Facades\Facade;

class LocalizationService extends Facade
{
    protected static function getFacadeAccessor() {
        return "Localization";
    }
}