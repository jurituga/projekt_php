@props(['service', 'avgRating' => 0, 'ratingCount' => 0, 'showDescription' => false])

<a href="{{ route('services.show', $service) }}" class="listing-card">
    <div class="listing-avatar av-service">{{ mb_strtoupper(mb_substr($service->freelancer->name, 0, 1)) }}</div>
    <div class="listing-body">
        <h3 class="listing-title">{{ $service->title }}</h3>
        <div class="listing-info">
            <span>{{ $service->freelancer->name }}</span>
            @php $type = $service->freelancer->freelancerProfile?->freelancer_type; @endphp
            @if(in_array($type, ['electrician', 'plumber'], true))
                <span class="listing-dot">&bull;</span>
                <span class="listing-tag" style="margin:0">{{ ucfirst($type) }}</span>
            @endif
        </div>
        @if($ratingCount > 0)
            <div class="rating-summary" style="margin:.2rem 0">
                {!! render_stars($avgRating) !!}
                <span class="rating-number">{{ number_format($avgRating, 1) }}</span>
                <span class="rating-count">({{ $ratingCount }})</span>
            </div>
        @endif
        @if(!empty($showDescription) && $service->description)
            <p class="listing-desc">{{ Str::limit($service->description, 120) }}</p>
        @endif
        <div class="listing-footer">
            <span class="listing-tag tag-price">{{ $service->price ? '$'.number_format($service->price, 2).' '.$service->price_type : 'Contact for price' }}</span>
        </div>
    </div>
    <span class="listing-arrow">&rsaquo;</span>
</a>
