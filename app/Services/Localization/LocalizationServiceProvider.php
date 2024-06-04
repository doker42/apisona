<?php
/**
 * Created by PhpStorm.
 * User: Viktor
 * Date: 30.09.2019
 * Time: 23:15
 */

namespace App\Services\Localization;


use Illuminate\Support\ServiceProvider;

class LocalizationServiceProvider extends ServiceProvider
{
    public function register() {
        $this->app->bind("Localization", '\App\Services\Localization\Localization');
    }
}