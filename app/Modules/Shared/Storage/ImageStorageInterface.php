<?php

namespace App\Modules\Shared\Storage;

use Illuminate\Http\UploadedFile;

interface ImageStorageInterface
{
    public function storePostImage(UploadedFile $image): string;
}
