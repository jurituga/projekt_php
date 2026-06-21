<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use App\Services\CvService;
use App\Services\DocumentUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CvController extends Controller
{
    public function index(): View
    {
        $cvs = auth()->user()->cvs()->orderByDesc('is_default')->orderByDesc('created_at')->get();

        return view('user.cvs', compact('cvs'));
    }

    public function store(Request $request, CvService $cvService): RedirectResponse
    {
        $request->validate([
            'cv_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        try {
            $cvService->uploadForUser(auth()->user(), $request->file('cv_file'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['cv_file' => $e->getMessage()]);
        }

        return redirect()->route('user.cvs.index')->with('success', 'CV uploaded successfully.');
    }

    public function setDefault(Cv $cv): RedirectResponse
    {
        abort_unless($cv->user_id === auth()->id(), 403);

        auth()->user()->cvs()->update(['is_default' => false]);
        $cv->update(['is_default' => true]);

        return redirect()->route('user.cvs.index');
    }

    public function destroy(Cv $cv, DocumentUploadService $uploader): RedirectResponse
    {
        abort_unless($cv->user_id === auth()->id(), 403);

        $uploader->delete(config('vacanto.upload_paths.cvs'), $cv->file_path);
        $cv->delete();

        return redirect()->route('user.cvs.index');
    }
}
