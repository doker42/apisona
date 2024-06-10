<?php

namespace App\Common;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageLocalPublic
{

    public object $storage;

    public function __construct()
    {
        $this->storage = Storage::disk('public');
    }


    /**
     * @param string $fileName
     * @return void
     */
    public function delete(string $fileName): void
    {
        if ($this->storage->exists($fileName)){
            $this->storage->delete($fileName);
        }
    }


    /**
     * @param string $dir
     * @param UploadedFile|string $file
     * @param string $filename
     * @return false|string
     */
    public function putAs(string $dir, UploadedFile|string $file, string $filename)
    {
        return $this->storage->putFileAs($dir, $file, $filename);
    }
}
