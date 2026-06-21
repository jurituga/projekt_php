@extends('layouts.app')

@section('title', $title ?? 'Dashboard')

@section('content')
<div class="container page-header">
    <h1>{{ $title ?? 'Dashboard' }}</h1>
    <p class="muted">This section will be fully ported in the next phase.</p>
</div>
@endsection
