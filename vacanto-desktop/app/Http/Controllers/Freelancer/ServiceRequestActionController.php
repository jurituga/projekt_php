<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\ServiceAvailability;
use App\Models\ServiceRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceRequestActionController extends Controller
{
    public function __construct(private NotificationService $notifications) {}
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'integer'],
            'action' => ['required', 'in:accept,reject,complete'],
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $allowedStatuses = match ($validated['action']) {
            'accept' => ['pending'],
            'reject' => ['pending', 'accepted'],
            'complete' => ['accepted'],
        };

        $serviceRequest = ServiceRequest::with(['service', 'requester'])
            ->where('id', $validated['request_id'])
            ->whereHas('service', fn ($q) => $q->where('freelancer_id', auth()->id()))
            ->whereIn('status', $allowedStatuses)
            ->first();

        if (! $serviceRequest) {
            return redirect()->route('freelancer.dashboard');
        }

        if ($validated['action'] === 'accept') {
            $serviceRequest->update(['status' => 'accepted']);
        } elseif ($validated['action'] === 'complete') {
            $serviceRequest->update(['status' => 'completed']);
        } else {
            $reason = trim($validated['rejection_reason'] ?? '');
            if ($reason === '') {
                return back()->with('error', 'Please provide a reason for the rejection.');
            }

            $serviceRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            if ($serviceRequest->booking_slot_id) {
                ServiceAvailability::where('id', $serviceRequest->booking_slot_id)
                    ->update(['is_booked' => false]);
            }
        }

        $requesterUrl = $serviceRequest->requester->role === UserRole::Company
            ? route('company.dashboard')
            : route('user.service-requests.index');

        $notification = match ($validated['action']) {
            'accept' => [
                'Service request accepted',
                'Your request for '.$serviceRequest->service->title.' was accepted.',
                'bell',
            ],
            'complete' => [
                'Service completed',
                $serviceRequest->service->title.' has been marked as completed. You can pay and leave a review.',
                'bell',
            ],
            default => [
                'Service request rejected',
                'Your request for '.$serviceRequest->service->title.' was rejected.',
                'bell',
            ],
        };

        $this->notifications->send(
            $serviceRequest->requester,
            $notification[0],
            $notification[1],
            $requesterUrl,
            $notification[2]
        );

        return redirect()->route('freelancer.dashboard')->with('success', 'Request updated.');
    }
}
