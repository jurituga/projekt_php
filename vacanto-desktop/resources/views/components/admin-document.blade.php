@props(['filename', 'type', 'label' => 'Document'])

@php
    $filename = $filename ?? null;
    $ext = $filename ? strtolower(pathinfo($filename, PATHINFO_EXTENSION)) : null;
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true);
    $isPdf = $ext === 'pdf';
    $url = $filename ? route('admin.documents.show', ['type' => $type, 'filename' => $filename]) : null;
@endphp

@if($filename && $url)
    <div class="admin-document-preview">
        @if($isImage)
            <img src="{{ $url }}" alt="{{ $label }} preview" style="max-width:400px;max-height:300px;border:1px solid var(--border);border-radius:var(--r-sm);display:block;margin-bottom:0.5rem;">
        @elseif($isPdf)
            <p class="muted" style="margin:0 0 .5rem;font-size:.85rem">PDF document uploaded.</p>
        @endif
        <a href="{{ $url }}" class="btn btn-small" target="_blank" rel="noopener">View / Download</a>
    </div>
@else
    <span class="muted">Not uploaded</span>
@endif
