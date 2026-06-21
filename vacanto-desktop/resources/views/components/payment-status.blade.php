@props(['request'])

@php
    $paymentStatus = $request->payment_status ?? 'unpaid';
    $payAmount = (float) ($request->payment_amount ?: $request->service?->price ?: 0);
@endphp

@if($paymentStatus === 'paid')
    <span class="status status-active">Paid ${{ number_format($request->payment_amount, 2) }}</span>
@elseif($paymentStatus === 'refunded')
    <span class="status status-rejected">Refunded</span>
@elseif($request->status === 'completed' && $payAmount > 0)
    @auth
        @if(in_array(auth()->user()->role, [\App\Enums\UserRole::User, \App\Enums\UserRole::Company], true) && $request->requester_id === auth()->id())
            <a href="{{ route('payments.show', $request) }}" class="btn btn-small btn-pay">Pay ${{ number_format($payAmount, 2) }}</a>
        @else
            <span class="muted">&mdash;</span>
        @endif
    @else
        <span class="muted">&mdash;</span>
    @endauth
@else
    <span class="muted">&mdash;</span>
@endif
