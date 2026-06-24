<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class DocumentUploadService
{
    public function store(?UploadedFile $file, string $diskPath, string $prefix, ?array $allowedMimes = null, ?int $maxSize = null): ?string
    {
        if (! $file) {
            return null;
        }

        $maxSize = $maxSize ?? config('vacanto.upload_max_doc_size');
        $allowedMimes = $allowedMimes ?? config('vacanto.allowed_doc_mimes');

        if ($file->getSize() > $maxSize) {
            throw new InvalidArgumentException('File too large (max 5MB).');
        }

        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new InvalidArgumentException('Invalid file type. Allowed: PDF, JPEG, PNG, GIF.');
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $filename = $prefix.'_'.time().'_'.random_int(1000, 9999).'.'.$ext;

        Storage::disk('vacanto')->putFileAs($diskPath, $file, $filename);

        return $filename;
    }

    public function storeCv(?UploadedFile $file, string $diskPath, string $prefix): ?string
    {
        return $this->store(
            $file,
            $diskPath,
            $prefix,
            config('vacanto.allowed_cv_mimes'),
            config('vacanto.upload_max_cv_size'),
        );
    }

    public function delete(string $diskPath, string $filename): void
    {
        Storage::disk('vacanto')->delete($diskPath.'/'.$filename);
    }

    public function absolutePath(string $diskPath, string $filename): string
    {
        $relative = trim($diskPath, '/').'/'.ltrim($filename, '/');
        $primary = Storage::disk('vacanto')->path($relative);

        if (is_file($primary)) {
            return $primary;
        }

        $nativeRoot = config('nativephp-internal.storage_path');
        if ($nativeRoot) {
            $alternate = rtrim($nativeRoot, '/').'/app/'.$relative;
            if (is_file($alternate)) {
                return $alternate;
            }
        }

        return $primary;
    }
}
