<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\FreelancerRating;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();
        $profile = auth()->user()->freelancerProfile;

        $requests = ServiceRequest::with(['service', 'requester'])
            ->whereHas('service', fn ($q) => $q->where('freelancer_id', $userId))
            ->latest()
            ->limit(15)
            ->get();

        $activeServicesCount = Service::where('freelancer_id', $userId)->where('status', 'active')->count();

        $services = Service::where('freelancer_id', $userId)
            ->withCount([
                'availability as open_slots_count' => fn ($q) => $q
                    ->where('available_date', '>=', now()->toDateString())
                    ->where('is_booked', false),
            ])
            ->latest()
            ->limit(10)
            ->get();

        $freelancerType = $profile?->freelancer_type ?? 'general';
        $isScheduled = in_array($freelancerType, ['electrician', 'plumber'], true);

        $avgRating = round(FreelancerRating::where('freelancer_id', $userId)->avg('rating') ?? 0, 1);
        $ratingCount = FreelancerRating::where('freelancer_id', $userId)->count();

        $totalEarned = ServiceRequest::whereHas('service', fn ($q) => $q->where('freelancer_id', $userId))
            ->where('payment_status', 'paid')
            ->sum('payment_amount');

        $pendingCount = $requests->where('status', 'pending')->count();

        return view('freelancer.dashboard', compact(
            'requests',
            'services',
            'activeServicesCount',
            'freelancerType',
            'isScheduled',
            'avgRating',
            'ratingCount',
            'totalEarned',
            'pendingCount'
        ));
    }
}
