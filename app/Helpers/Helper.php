<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Helper
{
    /**
     * @param Request $request
     * @return array|null
     */
    public static function getDomainInfo(Request $request): ?array
    {
        $data = [
            'host' => null,
            'subdomain' => null
        ];
        $originDomain = $request->header('origin');
        if (!is_null($originDomain)) {
            $parsed = parse_url($originDomain);
            $data['host'] = $parsed['host'];
            $data['subdomain'] = explode('.', $parsed['host'])[0];
            return $data;
        }

        return null;
    }


    /**
     * @param string $text
     * @param int $length
     * @return string
     */
    public static function getShortText(string $text, int $length): string
    {
        return strlen($text) > $length
            ? substr($text, 0, $length) . '...'
            : $text;
    }

    /**
     * @param  string  $refreshToken
     * @return array
     */
    public static function refreshTokenCookieParameters(string $refreshToken): array
    {
        return [
            'refresh_token',
            $refreshToken,
            config('passport.tokens_lifetime.refresh_token_remember_me'),
            null,
            null,
            false,
            true
        ];
    }


    /**
     * @param  string  $string
     * @param  string  $language
     * @return string
     */
    public static function getStringInLocal(string $string, string $language): string
    {
        $localized = $string;

        if (in_array($language, config('app.locales'))) {

            $current_language = App::getLocale();
            App::setLocale($language);
            $localized = __($localized);
            App::setLocale($current_language);
        }

        return $localized;
    }


    /**
     * @param $locale
     * @return array
     */
    public static function locales($locale = null): array
    {
        $locales = config('app.locales');

        if ($locale) {
            App::setLocale($locale);
        }

        $langs = [];
        foreach($locales as $locale) {
            $langs[] = [
                'value' => $locale,
                'label' => __($locale)
            ];
        }

        return $langs;
    }

}
