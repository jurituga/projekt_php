<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function ratingImage(string $filename): BinaryFileResponse
    {
        $path = config('vacanto.upload_paths.rating_images').'/'.$filename;

        if (! Storage::disk('vacanto')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('vacanto')->path($path));
    }
}
