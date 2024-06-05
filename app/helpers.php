<?php

use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;



/**
 * @return Authenticatable|User|null
 */
function user(): Authenticatable|User|null
{
    return auth()->guard('api')->user();
}





function arrayFilterOnly(array $array, array $only): array
{
    $should_filter = [];
    foreach ($only as $el) {
        if (isset($array[$el])) {
            $should_filter[$el] = $array[$el];
        }
    }

    return array_filter($should_filter);
}


function array_filter_except(array $array, array $except): array
{
    $not_filtered = [];
    foreach ($except as $el) {
        if (isset($array[$el])) {
            $not_filtered[$el] = $array[$el];
            unset($array[$el]);
        }
    }

    $array = array_filter($array);

    return array_merge($array, $not_filtered);
}


function array_except_filter(array $array, array $except): array
{
    $not_filtered = [];

    foreach ($array as $key => $value) {

        if (in_array($key, $except)) {
            $not_filtered[$key] = $value;
        }
    }

    $array = array_filter($array);

    return array_merge($array, $not_filtered);
}


function array_only(array $array, array $only): array
{
    if (!count($array) || !count($only)) {
        return $array;
    }

    $filtered = [];

    foreach ($array as $key => $value) {
        if (in_array($key, $only)) {
            $filtered[$key] = $value;
        }
    }

    return $filtered;
}


function array_except(array $array, array $except): array
{
    if (!count($array) || !count($except)) {
        return $array;
    }

    $filtered = [];

    foreach ($array as $key => $value) {
        if (!in_array($key, $except)) {
            $filtered[$key] = $value;
        }
    }

    return $filtered;
}


function remove_extra($string): string
{
    $string = preg_replace('/[^а-яёa-zа-я0-9`є\'"іїґ_,.!;:-]/iu', ' ', $string);

    return preg_replace('/\s+/', ' ', $string);
}


function is_set(array $data, string $key, $return = null)
{
    return !empty($data[$key]) ? $data[$key] : $return;
}



function getModelName(object $object): string|null
{
    $res = strtolower(class_basename(get_class($object)));
    return $res ?: null;
}


/**  get class name from string
 *   (example 'post', 'person_post', 'product_person'...)
 */
function getClassNameCustom(string $nameSpace, string $string, string $separator): string|null
{
    if (strlen($string) <= 1 || !in_array($separator, ['-', '_'])){
        return null;
    }

    return $nameSpace . toCamelCase($string, $separator);
}


function toCamelCase(string $string, string $separator): string|null
{
    if (strlen($string) <= 1 || !in_array($separator, ['-', '_'])){
        return null;
    }

    if (str_contains($string, $separator)) {

        $arr = explode($separator, $string);

        $res = '';

        foreach ($arr as $item) {
            $res .= ucfirst($item);
        }
    } else {
        $res = ucfirst($string);
    }

    return $res;
}


function getEntity(string $modelNickname, int $entityId, array $allowedEntities): null|object
{
    if (in_array($modelNickname, $allowedEntities)) {
        $entityClass = getClassNameCustom('\App\Models\\', $modelNickname, '_');
        $usesSoftDeletes = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($entityClass));
        if ($usesSoftDeletes) {
            return $entityClass::withTrashed()->where('id', $entityId)->first();
        } else {
            return $entityClass::where('id', $entityId)->first();
        }
    }

    return null;
}


function getSlug(string $string, int $cut=0, bool $unique = false): string
{
    $string = $string ?: Str::random(10);
    $string = $cut ? substr($string, 0, $cut) : $string;
    $string = $unique ? $string . ' ' . Str::random(5) : $string;

    return Str::slug($string, '-');
}


function hex2rgba($color, $opacity = false)
{

    $defaultColor = 'rgb(0,0,0)';

    // Return default color if no color provided
    if ( empty( $color ) ) {
        return $defaultColor;
    }

    // Ignore "#" if provided
    if ( $color[0] == '#' ) {
        $color = substr( $color, 1 );
    }

    // Check if color has 6 or 3 characters, get values
    if ( strlen($color) == 6 ) {
        $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
    } elseif ( strlen( $color ) == 3 ) {
        $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
    } else {
        return $defaultColor;
    }

    // Convert hex values to rgb values
    $rgb =  array_map( 'hexdec', $hex );

    // Check if opacity is set(rgba or rgb)
    if ( $opacity ) {
        if( abs( $opacity ) > 1 ) {
            $opacity = 1.0;
        }
        $output = 'rgba(' . implode( ",", $rgb ) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode( ",", $rgb ) . ')';
    }

    // Return rgb(a) color string
    return $output;
}

