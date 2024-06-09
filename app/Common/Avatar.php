<?php

namespace App\Common;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class Avatar
{
    public const IMAGE_ORIGIN = 'origin';
    public const IMAGE_BIG    = 'big';
    public const IMAGE_SMALL  = 'small';
    public const EXT_SVG = 'svg';
    public const DIR_AVATARS = '/avatars';


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


    /**
     * @param $image
     * @param $sizes
     * @return string|null
     */
    public static function store($image, $sizes = null): string|null
    {
        $filename = self::createFileName($image);

        $result = Storage::disk('public')->putFileAs(self::DIR_AVATARS, $image, $filename);

        if (!self::ifExtensionSvg($filename) && $filename && $sizes) {

            $res = self::generateThumbnails($image, $filename, $sizes);

            return count($res) == count($sizes) ? $filename : null;
        }

        return $result ? $filename : null;
    }


    /**
     * @param $image
     * @param $filename
     * @param $size
     * @return string|null
     */
    public static function reduceAndStore($image, $filename, $size): string|null
    {
        $imageManager = new ImageManager(new Driver());
        $image = $imageManager->read($image->path());
        $temp_dir = sys_get_temp_dir();
        $image->scale(height: $size['height']);
        $path = $temp_dir . '/' . $filename;

        $image->save($path);
        Storage::disk('public')->putFileAs(self::DIR_AVATARS, $path, $filename);

        return $filename;
    }


    /**
     * @param $image
     * @param $filename
     * @param $sizes
     * @return array
     */
    private static function generateThumbnails($image, $filename, $sizes): array
    {
        $res = [];
        /* reduce and store */
        foreach($sizes as $key => $value) {
            $cover_filename = $key . '_' . $filename;
            $res[] = self::reduceAndStore($image, $cover_filename, $value);
        }

        return $res;
    }


    /**
     * @param $filename
     * @param string|null $dir
     * @param $sizes
     * @return void
     */
    public static function deleteImages($filename, string $dir=null, $sizes=null): void
    {
        if($filename){
            $images_names = [$filename];
            if ($sizes) {
                foreach (array_keys($sizes) as $prefix) {
                    $images_names[] = $prefix . '_' . $filename;
                }
                foreach($images_names as $item) {

                    if ($dir) {
                        $item = $dir . '/' . $item;
                    }

                    self::delete($item);
                }
            } else {
                self::delete($filename);
            }
        }
    }


    /**
     * Remove file from Cloud storage using file_name
     *
     * @param $filename
     * @return void
     */
    public static function delete($filename): void
    {
        if (Storage::disk('public')->exists($filename)){
            Storage::disk('public')->delete($filename);
        }
    }


    /**
     * @param $name
     * @param array|null $sizes
     * @return null[]
     */
    public static function getThumbnails($name, array $sizes = null): array
    {
        $res = [
            'origin' => $name ? self::url($name) : null
        ];

        if ($sizes) {
            foreach ($sizes as $size) {
                $res += [
                    $size => $name ? (self::ifExtensionSvg($name) ?  self::url($name) : self::url($size . '_' . $name)) : null
                ];
            }
        }

        return $res;
    }


    /**
     * @param $fileName
     * @return string
     */
    public static  function url($fileName): string
    {
        return asset('storage' .  self::DIR_AVATARS . '/' . $fileName);
    }

}
