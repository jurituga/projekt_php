<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DocumentUploadService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function __invoke(string $type, string $filename, DocumentUploadService $uploader): BinaryFileResponse|Response
    {
        abort_unless(in_array($type, ['gov', 'cert'], true), 404);

        $filename = basename($filename);
        $diskPath = $type === 'gov'
            ? config('vacanto.upload_paths.government_ids')
            : config('vacanto.upload_paths.certifications');

        $fullPath = $uploader->absolutePath($diskPath, $filename);

        if (! file_exists($fullPath)) {
            abort(404, 'File not found.');
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeMap = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];
        $mime = $mimeMap[$ext] ?? 'application/octet-stream';

        $headers = ['Content-Type' => $mime];

        if ($mime === 'application/pdf') {
            return response()->file($fullPath, array_merge($headers, [
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]));
        }

        return response()->file($fullPath, $headers);
    }
}
