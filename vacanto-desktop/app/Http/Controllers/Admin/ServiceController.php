<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::with('freelancer')->latest()->get();

        return view('admin.services.index', compact('services'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);

        Service::where('id', $validated['service_id'])->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }
}
