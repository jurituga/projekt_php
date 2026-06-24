<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Cv;
use App\Models\JobApplication;
use App\Services\DocumentUploadService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CvDownloadController extends Controller
{
    public function __invoke(Cv $cv, DocumentUploadService $uploader): StreamedResponse|BinaryFileResponse|Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, 'Access denied.');
        }

        $allowed = $cv->user_id === $user->id || $user->isAdmin();

        if (! $allowed && $user->role === UserRole::Company) {
            $allowed = JobApplication::where('cv_id', $cv->id)
                ->whereHas('jobPosting.company', fn ($q) => $q->where('user_id', $user->id))
                ->exists();

            if (! $allowed) {
                $allowed = JobApplication::where('user_id', $cv->user_id)
                    ->whereHas('jobPosting.company', fn ($q) => $q->where('user_id', $user->id))
                    ->exists();
            }
        }

        abort_unless($allowed, 403, 'Access denied.');

        $path = config('vacanto.upload_paths.cvs');
        $fullPath = $uploader->absolutePath($path, $cv->file_path);

        if (! is_file($fullPath)) {
            abort(404, 'File not found on server.');
        }

        $safeName = basename($cv->file_name) ?: 'cv.pdf';

        $headers = [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-cache',
        ];

        if (config('nativephp-internal.running') || filter_var(env('NATIVEPHP_RUNNING', false), FILTER_VALIDATE_BOOL)) {
            return response()->file($fullPath, array_merge($headers, [
                'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            ]));
        }

        return response()->download($fullPath, $safeName, $headers);
    }
}
