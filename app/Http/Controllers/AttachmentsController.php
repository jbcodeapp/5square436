<?php

namespace App\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttachmentsController extends Controller
{
    public function attachment_download(Media $media)
    {
        $fullName = $media->name.".".last(explode(".",$media->file_name));
        return response()->download($media->getPath(), $fullName);
    }
}
