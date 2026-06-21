<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\FreelancerRating;
use App\Models\RatingImage;
use App\Models\ServiceRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RatingController extends Controller
{
    public function __construct(private NotificationService $notifications) {}
    public function create(ServiceRequest $serviceRequest): View|RedirectResponse
    {
        $request = $this->authorizedRequest($serviceRequest);
        if ($request instanceof RedirectResponse) {
            return $request;
        }

        if ($request->rating) {
            return view('ratings.create', [
                'serviceRequest' => $request,
                'alreadyRated' => true,
            ]);
        }

        return view('ratings.create', [
            'serviceRequest' => $request,
            'alreadyRated' => false,
        ]);
    }

    public function store(Request $httpRequest, ServiceRequest $serviceRequest): RedirectResponse
    {
        $request = $this->authorizedRequest($serviceRequest);
        if ($request instanceof RedirectResponse) {
            return $request;
        }

        if ($request->rating) {
            return $this->requestsRedirect()->with('error', 'You have already rated this service.');
        }

        $validated = $httpRequest->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:5000'],
            'review_images' => ['nullable', 'array', 'max:'.config('vacanto.upload_max_rating_images')],
            'review_images.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $uploaded = [];
        if ($httpRequest->hasFile('review_images')) {
            $path = config('vacanto.upload_paths.rating_images');
            foreach ($httpRequest->file('review_images') as $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $filename = uniqid('rev_', true).'.'.$ext;
                Storage::disk('vacanto')->putFileAs($path, $file, $filename);
                $uploaded[] = $filename;
            }
        }

        DB::transaction(function () use ($request, $validated, $uploaded) {
            $rating = FreelancerRating::create([
                'freelancer_id' => $request->service->freelancer_id,
                'reviewer_id' => auth()->id(),
                'service_request_id' => $request->id,
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]);

            foreach ($uploaded as $filename) {
                RatingImage::create([
                    'rating_id' => $rating->id,
                    'file_path' => $filename,
                ]);
            }

            $this->notifications->send(
                $request->service->freelancer,
                'New rating received',
                auth()->user()->name.' rated your service '.$validated['rating'].'/5 stars.',
                route('freelancer.dashboard'),
                'star'
            );
        });

        return $this->requestsRedirect()->with('success', 'Thank you! Your rating has been submitted.');
    }

    private function authorizedRequest(ServiceRequest $serviceRequest): ServiceRequest|RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, [UserRole::User, UserRole::Company], true), 403);

        $serviceRequest = ServiceRequest::with(['service.freelancer', 'rating'])
            ->where('id', $serviceRequest->id)
            ->where('requester_id', auth()->id())
            ->where('status', 'completed')
            ->first();

        if (! $serviceRequest) {
            return redirect()->route('home');
        }

        return $serviceRequest;
    }

    private function requestsRedirect(): RedirectResponse
    {
        if (auth()->user()->role === UserRole::Company) {
            return redirect()->route('company.dashboard');
        }

        return redirect()->route('user.service-requests.index');
    }
}
