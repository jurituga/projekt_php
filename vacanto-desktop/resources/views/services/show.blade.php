@extends('layouts.app')

@section('title', $service->title)

@section('content')
<div class="detail-header">
    <div class="container">
        <p class="breadcrumb"><a href="{{ route('services.index') }}">Services</a> &rarr; {{ $service->title }}</p>
        <h1>{{ $service->title }}
            @if($isTradeType)
                <span class="status status-active" style="font-size:.7rem;vertical-align:middle">{{ ucfirst($freelancerType) }}</span>
            @endif
        </h1>
        <div class="detail-meta">
            <span class="detail-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {{ $service->freelancer->name }}
            </span>
            @if($ratingCount > 0)
                <span class="detail-meta-dot"></span>
                <a href="#reviews" class="detail-meta-item" style="text-decoration:none;color:var(--gray-400)">
                    {!! render_stars($avgRating) !!}
                    <span style="font-weight:600">{{ number_format($avgRating, 1) }}</span>
                    <span>({{ $ratingCount }})</span>
                </a>
            @endif
            @auth
                @if(!auth()->user()->isAdmin() && auth()->id() !== $service->freelancer_id)
                    <span class="detail-meta-dot"></span>
                    <a href="{{ route('messages.chat', $service->freelancer) }}" class="btn btn-sm" style="background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.2);font-size:.78rem">Message</a>
                @endif
            @endauth
        </div>
        <div class="detail-tags">
            @if($hasPrice)
                <span class="detail-tag tag-price">${{ number_format($service->price, 2) }} {{ $service->price_type }}</span>
                <span class="detail-tag">Payment after completion</span>
            @else
                <span class="detail-tag">Contact for price</span>
            @endif
        </div>
    </div>
</div>

<div class="container detail-content">
    <div class="detail-section">
        <h2 class="detail-section-title">About this service</h2>
        <div class="content-block">{!! nl2br(e($service->description)) !!}</div>
    </div>

    @if($canRequest)
        <div class="card form-card" style="margin-bottom:1.5rem">
            <h2>Request this service</h2>
            <form method="POST" action="{{ route('services.request', $service) }}">
                @csrf
                @if($showSlotPicker)
                    <div class="form-group">
                        <label>Select a date &amp; time @if($hasBookableSlots || $isTradeType)<span class="required">*</span>@endif</label>
                        @if(empty($slotsByDate))
                            <p class="muted">No available time slots at the moment. Check back later.</p>
                        @else
                            @foreach($slotsByDate as $date => $daySlots)
                                <div class="avail-booking-day">
                                    <strong class="avail-day-heading">{{ \Carbon\Carbon::parse($date)->format('l, M j, Y') }}</strong>
                                    <div class="avail-dates-grid">
                                        @foreach($daySlots as $slot)
                                            <label class="avail-date-option">
                                                <input type="radio" name="slot_id" value="{{ $slot->id }}" required>
                                                <div>
                                                    <span class="date-label">{{ \Carbon\Carbon::parse($slot->slot_time)->format('g:i A') }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="4" placeholder="Describe your needs...">{{ old('message') }}</textarea>
                </div>

                @if(!$showSlotPicker || !empty($slotsByDate))
                    <button type="submit" class="btn btn-primary">Send Request</button>
                @endif
            </form>
        </div>
    @elseif(!auth()->check())
        <div class="card" style="text-align:center;padding:2rem;margin-bottom:1.5rem">
            <p><a href="{{ route('login') }}">Log in</a> or <a href="{{ route('register') }}">register</a> to request this service.</p>
        </div>
    @endif

    @if($ratingCount > 0)
    <div class="detail-section" id="reviews">
        <h2 class="detail-section-title">Reviews ({{ $ratingCount }})</h2>
        <div class="rating-summary" style="margin-bottom:1rem;">
            {!! render_stars($avgRating) !!}
            <span class="rating-number">{{ number_format($avgRating, 1) }}</span>
            <span class="rating-count">({{ $ratingCount }} review{{ $ratingCount > 1 ? 's' : '' }})</span>
        </div>
        @foreach($reviews as $rev)
            <div class="review-card">
                <div class="review-header">
                    {!! render_stars($rev->rating) !!}
                    <span class="review-author">{{ $rev->reviewer->name }}</span>
                    <span class="review-date">{{ $rev->created_at->format('M j, Y') }}</span>
                </div>
                @if($rev->review)
                    <p class="review-text">{!! nl2br(e($rev->review)) !!}</p>
                @endif
                @if($rev->images->isNotEmpty())
                    <div class="review-images">
                        @foreach($rev->images as $img)
                            <a href="{{ route('media.rating', $img->file_path) }}" target="_blank" class="review-img-thumb">
                                <img src="{{ route('media.rating', $img->file_path) }}" alt="Review photo" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
