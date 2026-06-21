@if(in_array($application->status, ['pending', 'viewed']))
    <form method="POST" action="{{ route('company.applications.action') }}" style="display:inline">
        @csrf
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        <input type="hidden" name="action" value="accept">
        <button type="submit" class="btn btn-small">Accept</button>
    </form>
    <form method="POST" action="{{ route('company.applications.action') }}" style="display:inline">
        @csrf
        <input type="hidden" name="application_id" value="{{ $application->id }}">
        <input type="hidden" name="action" value="reject">
        <button type="submit" class="btn btn-small btn-danger">Reject</button>
    </form>
@endif
