<?php

namespace App\Services;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class CvService
{
    public function __construct(private DocumentUploadService $uploader) {}

    public function uploadForUser(User $user, UploadedFile $file, ?bool $setDefault = null): Cv
    {
        $path = config('vacanto.upload_paths.cvs');

        try {
            $filename = $this->uploader->storeCv($file, $path, 'cv_'.$user->id);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }

        if (! $filename) {
            throw new InvalidArgumentException('Upload failed. Try again.');
        }

        if ($setDefault === null) {
            $setDefault = $user->cvs()->count() === 0;
        }

        if ($setDefault) {
            $user->cvs()->update(['is_default' => false]);
        }

        return Cv::create([
            'user_id' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filename,
            'is_default' => $setDefault,
        ]);
    }
}
