<nav class="dashboard-nav" style="margin-bottom:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap">
    @if($role === 'user')
        <a href="{{ route('user.dashboard') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('user.profile.edit') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('user.profile.*') ? 'active' : '' }}">Profile</a>
        <a href="{{ route('user.cvs.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('user.cvs.*') ? 'active' : '' }}">My CVs</a>
        <a href="{{ route('user.applications.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('user.applications.*') ? 'active' : '' }}">Applications</a>
        <a href="{{ route('user.service-requests.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('user.service-requests.*') ? 'active' : '' }}">Service Requests</a>
    @elseif($role === 'company')
        <a href="{{ route('company.dashboard') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('company.jobs.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('company.jobs.*') ? 'active' : '' }}">My Jobs</a>
        <a href="{{ route('company.applications.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('company.applications.*') ? 'active' : '' }}">Applications</a>
        <a href="{{ route('company.profile.edit') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('company.profile.*') ? 'active' : '' }}">Company Profile</a>
    @elseif($role === 'freelancer')
        <a href="{{ route('freelancer.dashboard') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('freelancer.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('freelancer.services.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('freelancer.services.*') ? 'active' : '' }}">My Services</a>
        <a href="{{ route('freelancer.requests.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('freelancer.requests.*') ? 'active' : '' }}">Requests</a>
        <a href="{{ route('freelancer.earnings.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('freelancer.earnings.*') ? 'active' : '' }}">Earnings</a>
        <a href="{{ route('freelancer.profile.edit') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('freelancer.profile.*') ? 'active' : '' }}">Profile</a>
    @elseif($role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Manage Users</a>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">Manage Jobs</a>
        <a href="{{ route('admin.services.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">Manage Services</a>
    @endif
</nav>
