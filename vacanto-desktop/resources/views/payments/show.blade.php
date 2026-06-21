@extends('layouts.app')

@section('title', 'Pay for Service')

@section('content')
<div class="container" style="max-width:560px;">
    <p class="breadcrumb">
        <a href="{{ auth()->user()->role === \App\Enums\UserRole::Company ? route('company.dashboard') : route('user.service-requests.index') }}">My Requests</a>
        &rarr; Payment
    </p>

    <div class="card form-card">
        <h1>Pay for Service</h1>

        <div class="pay-summary">
            <table class="pay-details-table">
                <tr><td class="pay-label">Service</td><td>{{ $serviceRequest->service->title }}</td></tr>
                <tr><td class="pay-label">Freelancer</td><td>{{ $serviceRequest->service->freelancer->name }}</td></tr>
                @if($serviceRequest->booking_date)
                <tr>
                    <td class="pay-label">Date</td>
                    <td>
                        {{ $serviceRequest->booking_date->format('M j, Y') }}
                        @if($serviceRequest->booking_time)
                            at {{ \Carbon\Carbon::parse($serviceRequest->booking_time)->format('g:i A') }}
                        @endif
                    </td>
                </tr>
                @endif
                <tr><td class="pay-label">Amount</td><td class="pay-amount">${{ number_format($amount, 2) }}</td></tr>
            </table>
        </div>

        @if(!$stripeKey || !config('vacanto.stripe.secret'))
            <div class="alert alert-error">Stripe is not configured. Add STRIPE_PUBLISHABLE_KEY and STRIPE_SECRET_KEY to your .env file.</div>
        @else
            <div id="pay-error" class="alert alert-error" style="display:none;"></div>
            <div id="pay-success" class="alert alert-success" style="display:none;">
                Payment successful!
                <a href="{{ auth()->user()->role === \App\Enums\UserRole::Company ? route('company.dashboard') : route('user.service-requests.index') }}">Back to my requests</a>
            </div>

            <form id="payment-form" style="margin-top:1.5rem;">
                <div class="form-group">
                    <label for="card-element">Card details</label>
                    <div id="card-element" class="stripe-card-element"></div>
                </div>
                <button id="pay-btn" type="submit" class="btn btn-primary btn-pay" style="width:100%;margin-top:1rem;">
                    Pay ${{ number_format($amount, 2) }}
                </button>
            </form>

            <p class="form-hint" style="margin-top:1rem;text-align:center;">
                <small>Payments are processed securely by Stripe. Your card details never touch our server.</small>
            </p>
        @endif
    </div>
</div>
@endsection

@if($stripeKey && config('vacanto.stripe.secret'))
@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function() {
    const stripe = Stripe(@json($stripeKey));
    const elements = stripe.elements();
    const card = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#1a1a2e',
                fontFamily: '"Inter", "Segoe UI", sans-serif',
                '::placeholder': { color: '#9ca3af' }
            },
            invalid: { color: '#ef4444' }
        }
    });
    card.mount('#card-element');

    const form = document.getElementById('payment-form');
    const btn  = document.getElementById('pay-btn');
    const errEl = document.getElementById('pay-error');
    const okEl  = document.getElementById('pay-success');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const amountLabel = 'Pay ${{ number_format($amount, 2) }}';

    function showError(msg) {
        errEl.textContent = msg;
        errEl.style.display = 'block';
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        btn.disabled = true;
        btn.textContent = 'Processing...';
        errEl.style.display = 'none';

        let res = await fetch(@json(route('payments.intent', $serviceRequest)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: '_token=' + encodeURIComponent(csrf)
        });
        let data = await res.json();

        if (data.error) {
            showError(data.error);
            btn.disabled = false;
            btn.textContent = amountLabel;
            return;
        }

        const {error, paymentIntent} = await stripe.confirmCardPayment(data.clientSecret, {
            payment_method: { card: card }
        });

        if (error) {
            showError(error.message);
            btn.disabled = false;
            btn.textContent = amountLabel;
            return;
        }

        if (paymentIntent.status === 'succeeded') {
            await fetch(@json(route('payments.confirm', $serviceRequest)), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: '_token=' + encodeURIComponent(csrf)
            });
            form.style.display = 'none';
            okEl.style.display = 'block';
        } else {
            showError('Unexpected payment status: ' + paymentIntent.status);
            btn.disabled = false;
            btn.textContent = amountLabel;
        }
    });
})();
</script>
@endpush
@endif
