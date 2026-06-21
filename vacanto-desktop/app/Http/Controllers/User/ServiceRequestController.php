<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    public function index(): View
    {
        $serviceRequests = ServiceRequest::with(['service.freelancer', 'rating'])
            ->where('requester_id', auth()->id())
            ->latest()
            ->get();

        return view('user.service_requests', compact('serviceRequests'));
    }
}
