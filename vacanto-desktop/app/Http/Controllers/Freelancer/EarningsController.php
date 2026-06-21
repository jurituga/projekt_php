<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class EarningsController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $totalEarned = ServiceRequest::whereHas('service', fn ($q) => $q->where('freelancer_id', $userId))
            ->where('payment_status', 'paid')
            ->sum('payment_amount');

        $paidCount = ServiceRequest::whereHas('service', fn ($q) => $q->where('freelancer_id', $userId))
            ->where('payment_status', 'paid')
            ->count();

        $payments = ServiceRequest::with(['service', 'requester'])
            ->whereHas('service', fn ($q) => $q->where('freelancer_id', $userId))
            ->where('payment_status', 'paid')
            ->orderByDesc('paid_at')
            ->limit(20)
            ->get();

        return view('freelancer.earnings.index', compact('totalEarned', 'paidCount', 'payments'));
    }
}
