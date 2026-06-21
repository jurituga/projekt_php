@props(['job', 'showDescription' => false])

<a href="{{ route('jobs.show', $job) }}" class="listing-card">
    <div class="listing-avatar av-job">{{ mb_strtoupper(mb_substr($job->company->company_name, 0, 1)) }}</div>
    <div class="listing-body">
        <h3 class="listing-title">{{ $job->title }}</h3>
        <div class="listing-info">
            <span>{{ $job->company->company_name }}</span>
            <span class="listing-dot">&bull;</span>
            <span>{{ $job->location ?? 'Remote' }}</span>
        </div>
        @if(!empty($showDescription) && $job->description)
            <p class="listing-desc">{{ Str::limit($job->description, 120) }}</p>
        @endif
        <div class="listing-footer">
            <span class="listing-tag tag-type">{{ str_replace('_', '-', $job->job_type) }}</span>
            @if($job->salary_min || $job->salary_max)
                <span class="listing-tag tag-salary">${{ $job->salary_min ? number_format($job->salary_min) : '—' }} – ${{ $job->salary_max ? number_format($job->salary_max) : '—' }}</span>
            @endif
            <span class="listing-date">{{ $job->created_at->format('M j') }}</span>
        </div>
    </div>
    <span class="listing-arrow">&rsaquo;</span>
</a>
