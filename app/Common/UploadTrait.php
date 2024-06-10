<?php

namespace App\Common;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait UploadTrait
{
    /**
     * @param UploadedFile $file
     * @return string
     */
    public static function createFileName(UploadedFile $file): string
    {
        $ext = $file->getClientOriginalExtension();
        $hash = md5(Str::random(10) . time());

        return $hash . '.' . $ext;
    }



    /**
     * @param string $filename
     * @return bool
     */
    public static function ifExtensionSvg(string $filename): bool
    {
        $ext = explode('.', $filename);

        return isset($ext[1]) && $ext[1] == self::EXT_SVG;
    }
}
