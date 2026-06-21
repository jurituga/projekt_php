@props(['filename', 'type'])

@php
    $filename = $filename ?? null;
    $ext = $filename ? strtolower(pathinfo($filename, PATHINFO_EXTENSION)) : null;
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true);
    $url = $filename ? route('admin.documents.show', ['type' => $type, 'filename' => $filename]) : null;
@endphp

@if($filename && $url)
    @if($isImage)
        <img src="{{ $url }}" alt="Document preview" style="max-width:400px;max-height:300px;border:1px solid var(--color-border);border-radius:var(--radius-sm);display:block;margin-bottom:0.5rem;">
    @endif
    <a href="{{ $url }}" class="btn btn-small" target="_blank" rel="noopener">View / Download</a>
@else
    <span class="muted">Not uploaded</span>
@endif
