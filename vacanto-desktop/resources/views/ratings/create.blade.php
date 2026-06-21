@extends('layouts.app')

@section('title', 'Rate Freelancer')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Rate Freelancer</h1>
    </div>

    <div class="card" style="margin-bottom:1.25rem">
        <p><strong>Service:</strong> {{ $serviceRequest->service->title }}</p>
        <p><strong>Freelancer:</strong> {{ $serviceRequest->service->freelancer->name }}</p>
    </div>

    @if($alreadyRated)
        <div class="alert alert-success">You have already rated this service.</div>
        <p><a href="{{ auth()->user()->role === \App\Enums\UserRole::Company ? route('company.dashboard') : route('user.service-requests.index') }}">&larr; Back to My Requests</a></p>
    @else
        <form method="POST" action="{{ route('ratings.store', $serviceRequest) }}" enctype="multipart/form-data" class="form-card">
            @csrf

            <div class="form-group">
                <label>Rating <span class="required">*</span></label>
                <div class="star-rating-input">
                    @for($i = 5; $i >= 1; $i--)
                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" @checked(old('rating') == $i) required>
                        <label for="star{{ $i }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">&#9733;</label>
                    @endfor
                </div>
            </div>

            <div class="form-group">
                <label for="review">Review (optional)</label>
                <textarea id="review" name="review" rows="4" placeholder="Share your experience with this freelancer...">{{ old('review') }}</textarea>
            </div>

            <div class="form-group">
                <label for="review_images">Photos (optional, max {{ config('vacanto.upload_max_rating_images') }})</label>
                <span class="form-hint">Upload photos of the completed work. JPG, PNG, GIF or WebP, max 5 MB each.</span>
                <input type="file" id="review_images" name="review_images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                <div class="image-preview-grid" id="imagePreviewGrid"></div>
            </div>

            <button type="submit" class="btn btn-primary">Submit Rating</button>
        </form>
        <p style="margin-top:1rem"><a href="{{ auth()->user()->role === \App\Enums\UserRole::Company ? route('company.dashboard') : route('user.service-requests.index') }}">&larr; Back to My Requests</a></p>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('review_images');
    var grid = document.getElementById('imagePreviewGrid');
    if (!input || !grid) return;

    input.addEventListener('change', function () {
        grid.innerHTML = '';
        var files = Array.from(this.files);
        var max = {{ config('vacanto.upload_max_rating_images') }};
        if (files.length > max) {
            alert('You can upload a maximum of ' + max + ' images.');
            this.value = '';
            return;
        }
        files.forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                var thumb = document.createElement('div');
                thumb.className = 'img-preview-thumb';
                thumb.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                grid.appendChild(thumb);
            };
            reader.readAsDataURL(file);
        });
    });
});
</script>
@endpush
