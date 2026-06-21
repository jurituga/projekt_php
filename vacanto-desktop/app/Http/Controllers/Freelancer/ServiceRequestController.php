<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    public function index(): View
    {
        $requests = ServiceRequest::with(['service', 'requester'])
            ->whereHas('service', fn ($q) => $q->where('freelancer_id', auth()->id()))
            ->latest()
            ->get();

        return view('freelancer.requests.index', compact('requests'));
    }
}
