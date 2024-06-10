<?php

namespace App\Common;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AvatarManager
{
    use UploadTrait;
    public const IMAGE_ORIGIN = 'origin';
    public const IMAGE_BIG    = 'big';
    public const IMAGE_SMALL  = 'small';
    public const EXT_SVG = 'svg';
    public const DIR_AVATARS = '/avatars';


    public StorageLocalPublic $storage;

    public function __construct(StorageLocalPublic $storage)
    {
        $this->storage = $storage;
    }




    /**
     * @param $image
     * @param $sizes
     * @return string|null
     */
    public function store($image, $sizes = null): string|null
    {
        $filename = self::createFileName($image);

        $result = $this->storage->putAs(self::DIR_AVATARS, $image, $filename);

        if (!self::ifExtensionSvg($filename) && $filename && $sizes) {

            $res = $this->generateThumbnails($image, $filename, $sizes);

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
    public function reduceAndStore($image, $filename, $size): string|null
    {
        $imageManager = new ImageManager(new Driver());
        $image = $imageManager->read($image->path());
        $temp_dir = sys_get_temp_dir();
        $image->scale(height: $size['height']);
        $path = $temp_dir . '/' . $filename;

        $image->save($path);
        $this->storage->putAs(self::DIR_AVATARS, $path, $filename);

        return $filename;
    }





    /**
     * @param $image
     * @param $filename
     * @param $sizes
     * @return array
     */
    private function generateThumbnails($image, $filename, $sizes): array
    {
        $res = [];
        /* reduce and store */
        foreach($sizes as $key => $value) {
            $cover_filename = $key . '_' . $filename;
            $res[] = $this->reduceAndStore($image, $cover_filename, $value);
        }

        return $res;
    }



    /**
     * @param $filename
     * @param string|null $dir
     * @param $sizes
     * @return void
     */
    public function deleteAll($filename, string $dir=null, $sizes=null): void
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

                    $this->delete($item);
                }
            } else {
                $this->delete($filename);
            }
        }
    }



    /**
     * Remove file from Cloud storage using file_name
     *
     * @param $filename
     * @return void
     */
    public function delete($filename): void
    {
        $this->storage->delete($filename);
    }


    /**
     * @param $name
     * @param array|null $sizes
     * @return null[]
     */
    public function getThumbnails($name, array $sizes = null): array
    {
        $res = [
            'origin' => $name ? $this->url($name) : null
        ];

        if ($sizes) {
            foreach ($sizes as $size) {
                $res += [
                    $size => $name ? ($this->ifExtensionSvg($name) ?  self::url($name) : self::url($size . '_' . $name)) : null
                ];
            }
        }

        return $res;
    }


    /**
     * @param $fileName
     * @return string
     */
    public function url($fileName): string
    {
        return asset('storage' .  self::DIR_AVATARS . '/' . $fileName);
    }

}
