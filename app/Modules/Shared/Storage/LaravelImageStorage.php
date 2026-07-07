<?php

namespace App\Modules\Shared\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class LaravelImageStorage implements ImageStorageInterface
{
    public function storePostImage(UploadedFile $image): string
    {
        return Storage::disk('public')->putFile('posts', $image);
    }
}
