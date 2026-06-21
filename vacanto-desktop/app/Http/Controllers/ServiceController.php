<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\FreelancerRating;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\ServiceRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private NotificationService $notifications) {}
    public function index(Request $request): View
    {
        $search = trim($request->input('q', ''));

        $services = Service::query()
            ->active()
            ->with(['freelancer.freelancerProfile'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $freelancerIds = $services->pluck('freelancer_id')->unique();
        $ratingStats = $freelancerIds->isEmpty()
            ? collect()
            : FreelancerRating::query()
                ->selectRaw('freelancer_id, AVG(rating) as avg_rating, COUNT(*) as rating_count')
                ->whereIn('freelancer_id', $freelancerIds)
                ->groupBy('freelancer_id')
                ->get()
                ->keyBy('freelancer_id');

        $services->each(function (Service $service) use ($ratingStats) {
            $stats = $ratingStats->get($service->freelancer_id);
            $service->avg_rating = $stats->avg_rating ?? 0;
            $service->rating_count = $stats->rating_count ?? 0;
        });

        return view('services.index', compact('services', 'search'));
    }

    public function show(Service $service): View|RedirectResponse
    {
        if ($service->status !== 'active') {
            return redirect()->route('services.index');
        }

        $service->load('freelancer.freelancerProfile');

        $freelancerType = $service->freelancer->freelancerProfile?->freelancer_type ?? 'general';
        $isTradeType = in_array($freelancerType, ['electrician', 'plumber'], true);

        $avgRating = round(FreelancerRating::where('freelancer_id', $service->freelancer_id)->avg('rating') ?? 0, 1);
        $ratingCount = FreelancerRating::where('freelancer_id', $service->freelancer_id)->count();

        $reviews = FreelancerRating::with(['reviewer', 'images'])
            ->where('freelancer_id', $service->freelancer_id)
            ->latest()
            ->limit(10)
            ->get();

        $slotsByDate = $this->availableSlotsByDate($service);
        $hasBookableSlots = ! empty($slotsByDate);
        $showSlotPicker = $isTradeType || $hasBookableSlots;

        $canRequest = auth()->check()
            && in_array(auth()->user()->role, [UserRole::User, UserRole::Company], true);

        $hasPrice = $service->price !== null && (float) $service->price > 0;

        return view('services.show', compact(
            'service',
            'freelancerType',
            'isTradeType',
            'avgRating',
            'ratingCount',
            'reviews',
            'slotsByDate',
            'hasBookableSlots',
            'showSlotPicker',
            'canRequest',
            'hasPrice'
        ));
    }

    public function request(Request $request, Service $service): RedirectResponse
    {
        if ($service->status !== 'active') {
            return redirect()->route('services.index');
        }

        if (! auth()->check() || ! in_array(auth()->user()->role, [UserRole::User, UserRole::Company], true)) {
            return redirect()->route('login');
        }

        $service->load('freelancer.freelancerProfile');
        $freelancerType = $service->freelancer->freelancerProfile?->freelancer_type ?? 'general';
        $isTradeType = in_array($freelancerType, ['electrician', 'plumber'], true);
        $hasBookableSlots = ! empty($this->availableSlotsByDate($service));
        $hasPrice = $service->price !== null && (float) $service->price > 0;

        $validated = $request->validate([
            'message' => ['nullable', 'string'],
            'slot_id' => [($hasBookableSlots || $isTradeType) ? 'required' : 'nullable', 'integer'],
        ]);

        $chosenSlot = null;
        if ($hasBookableSlots || $isTradeType) {
            $chosenSlot = ServiceAvailability::where('id', $validated['slot_id'] ?? 0)
                ->where('service_id', $service->id)
                ->where('is_booked', false)
                ->first();

            if (! $chosenSlot) {
                return back()->with('error', 'The selected time slot is no longer available. Please choose another.');
            }
        }

        DB::transaction(function () use ($service, $validated, $chosenSlot, $hasPrice) {
            ServiceRequest::create([
                'service_id' => $service->id,
                'requester_id' => auth()->id(),
                'message' => $validated['message'] ?? null,
                'booking_date' => $chosenSlot?->available_date,
                'booking_time' => $chosenSlot?->slot_time,
                'booking_slot_id' => $chosenSlot?->id,
                'payment_amount' => $hasPrice ? (float) $service->price : null,
                'status' => 'pending',
            ]);

            if ($chosenSlot) {
                $chosenSlot->update(['is_booked' => true]);
            }
        });

        $this->notifications->send(
            $service->freelancer,
            'New service request',
            auth()->user()->name.' requested your service: '.$service->title,
            route('freelancer.requests.index'),
            'bell'
        );

        $message = 'Service request sent'.($chosenSlot ? ' and time slot booked' : '').'. The freelancer will respond shortly.';
        if ($hasPrice) {
            $message .= ' You will be able to pay once the service is completed.';
        }

        return redirect()->route('services.show', $service)->with('success', $message);
    }

    private function availableSlotsByDate(Service $service): array
    {
        $slots = ServiceAvailability::where('service_id', $service->id)
            ->where('available_date', '>=', now()->toDateString())
            ->where('is_booked', false)
            ->orderBy('available_date')
            ->orderBy('slot_time')
            ->get();

        return $slots->groupBy(fn ($s) => $s->available_date->format('Y-m-d'))->all();
    }
}
