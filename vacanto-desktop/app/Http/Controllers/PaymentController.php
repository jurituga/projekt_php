<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ServiceRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function __construct(private NotificationService $notifications) {}
    public function show(ServiceRequest $serviceRequest): View|RedirectResponse
    {
        $request = $this->authorizedRequest($serviceRequest);
        if ($request instanceof RedirectResponse) {
            return $request;
        }

        if ($error = $this->validatePayable($request)) {
            return $this->requestsRedirect()->with('error', $error);
        }

        $amount = $this->paymentAmount($request);

        return view('payments.show', [
            'serviceRequest' => $request,
            'amount' => $amount,
            'stripeKey' => config('vacanto.stripe.key'),
        ]);
    }

    public function createIntent(ServiceRequest $serviceRequest): JsonResponse
    {
        $request = $this->authorizedRequest($serviceRequest);
        if ($request instanceof RedirectResponse) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($error = $this->validatePayable($request)) {
            return response()->json(['error' => $error]);
        }

        if (! config('vacanto.stripe.secret')) {
            return response()->json(['error' => 'Stripe is not configured.']);
        }

        Stripe::setApiKey(config('vacanto.stripe.secret'));
        $amountCents = (int) round($this->paymentAmount($request) * 100);

        try {
            if ($request->stripe_payment_intent) {
                $intent = PaymentIntent::retrieve($request->stripe_payment_intent);
                if ($intent->status === 'succeeded') {
                    return response()->json(['error' => 'Already paid.']);
                }
            } else {
                $intent = PaymentIntent::create([
                    'amount' => $amountCents,
                    'currency' => config('vacanto.stripe.currency', 'usd'),
                    'metadata' => [
                        'request_id' => $request->id,
                        'requester_id' => auth()->id(),
                        'service' => $request->service->title,
                    ],
                ]);
                $request->update(['stripe_payment_intent' => $intent->id]);
            }

            return response()->json(['clientSecret' => $intent->client_secret]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function confirm(ServiceRequest $serviceRequest): JsonResponse
    {
        $request = $this->authorizedRequest($serviceRequest);
        if ($request instanceof RedirectResponse) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (! $request->stripe_payment_intent) {
            return response()->json(['error' => 'No payment intent found.']);
        }

        if (! config('vacanto.stripe.secret')) {
            return response()->json(['error' => 'Stripe is not configured.']);
        }

        Stripe::setApiKey(config('vacanto.stripe.secret'));

        try {
            $intent = PaymentIntent::retrieve($request->stripe_payment_intent);
            if ($intent->status === 'succeeded') {
                $request->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'payment_amount' => $this->paymentAmount($request),
                ]);

                $this->notifications->send(
                    $request->service->freelancer,
                    'Payment received',
                    auth()->user()->name.' paid $'.number_format($this->paymentAmount($request), 2).' for '.$request->service->title,
                    route('freelancer.earnings.index'),
                    'payment'
                );

                return response()->json(['success' => true]);
            }

            return response()->json(['error' => 'Payment not confirmed. Status: '.$intent->status]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    private function authorizedRequest(ServiceRequest $serviceRequest): ServiceRequest|RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, [UserRole::User, UserRole::Company], true), 403);

        if ($serviceRequest->requester_id !== auth()->id()) {
            return $this->requestsRedirect()->with('error', 'Service request not found.');
        }

        $serviceRequest->load(['service.freelancer']);

        return $serviceRequest;
    }

    private function validatePayable(ServiceRequest $request): ?string
    {
        if ($request->status !== 'completed') {
            return 'You can only pay for completed services.';
        }

        if (($request->payment_status ?? 'unpaid') === 'paid') {
            return 'This service has already been paid.';
        }

        if ($this->paymentAmount($request) <= 0) {
            return 'No payment required for this service.';
        }

        return null;
    }

    private function paymentAmount(ServiceRequest $request): float
    {
        return (float) ($request->payment_amount ?: $request->service->price ?: 0);
    }

    private function requestsRedirect(): RedirectResponse
    {
        if (auth()->user()->role === UserRole::Company) {
            return redirect()->route('company.dashboard');
        }

        return redirect()->route('user.service-requests.index');
    }
}
