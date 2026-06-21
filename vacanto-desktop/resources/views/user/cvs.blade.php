@extends('layouts.app')

@section('title', 'My CVs')

@section('content')
<div class="container">
    <x-dashboard-nav role="user" />

    <h1>My CVs</h1>

    <div class="card form-card">
        <h2>Upload CV (PDF, max 5MB)</h2>
        <form method="POST" action="{{ route('user.cvs.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="cv_file">PDF file</label>
                <input type="file" id="cv_file" name="cv_file" accept="application/pdf" required>
                @error('cv_file')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>

    <h2>Your CVs</h2>
    @if($cvs->isEmpty())
        <p class="muted">No CVs uploaded yet.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>File name</th>
                    <th>Default</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cvs as $cv)
                    <tr>
                        <td>{{ $cv->file_name }}</td>
                        <td>{{ $cv->is_default ? 'Yes' : 'No' }}</td>
                        <td>{{ $cv->created_at?->format('M j, Y') ?? '—' }}</td>
                        <td>
                            <a href="{{ route('cvs.download', $cv) }}" class="btn btn-small">Download</a>
                            @if(!$cv->is_default)
                                <form method="POST" action="{{ route('user.cvs.default', $cv) }}" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-small">Set default</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('user.cvs.destroy', $cv) }}" style="display:inline" onsubmit="return confirm('Delete this CV?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
