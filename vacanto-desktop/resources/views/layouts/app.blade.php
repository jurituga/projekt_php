<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vacanto') | Vacanto</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect rx='18' width='100' height='100' fill='%230d9488'/><text x='50' y='72' font-size='62' font-weight='800' text-anchor='middle' fill='white'>V</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>

<div class="nav-overlay" id="navOverlay"></div>

<header class="site-header" id="siteHeader">
    <div class="header-inner container">
        <a href="{{ route('home') }}" class="logo">
            <span class="logo-icon">V</span>
            <span class="logo-text">Vacanto</span>
        </a>

        <button class="nav-toggle" id="navToggle" type="button" aria-label="Toggle navigation" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="mainNav">
            <div class="nav-links">
                <a href="{{ route('home') }}" class="nav-link">Home</a>
                <a href="{{ route('jobs.index') }}" class="nav-link">Jobs</a>
                <a href="{{ route('services.index') }}" class="nav-link">Services</a>
            </div>

            <div class="nav-actions">
                @auth
                    @php
                        $unreadNotifications = app(\App\Services\NotificationService::class)->unreadCount(auth()->id());
                    @endphp
                    <a href="{{ route('notifications.index') }}" class="nav-link nav-messages">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="nav-label">Notifications</span>
                        @if($unreadNotifications > 0)
                            <span class="msg-badge" id="notifBadge">{{ $unreadNotifications }}</span>
                        @else
                            <span class="msg-badge" id="notifBadge" style="display:none"></span>
                        @endif
                    </a>
                    @if(!auth()->user()->isAdmin())
                        @php
                            $unreadMessages = app(\App\Services\MessageService::class)->unreadCount(auth()->id());
                        @endphp
                        <a href="{{ route('messages.inbox') }}" class="nav-link nav-messages">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span class="nav-label">Messages</span>
                            @if($unreadMessages > 0)
                                <span class="msg-badge" id="msgBadge">{{ $unreadMessages }}</span>
                            @else
                                <span class="msg-badge" id="msgBadge" style="display:none"></span>
                            @endif
                        </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin Panel</a>
                    @elseif(auth()->user()->role === \App\Enums\UserRole::Company)
                        <a href="{{ route('company.dashboard') }}" class="nav-link">Dashboard</a>
                    @elseif(auth()->user()->role === \App\Enums\UserRole::Freelancer)
                        <a href="{{ route('freelancer.dashboard') }}" class="nav-link">Dashboard</a>
                        <a href="{{ route('freelancer.services.index') }}" class="nav-link">My Services</a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="nav-link">Dashboard</a>
                        <a href="{{ route('user.cvs.index') }}" class="nav-link">My CVs</a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">
                            Logout <span class="nav-user-name">({{ auth()->user()->name }})</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
                @endauth
            </div>
        </nav>
    </div>
</header>

<main class="main-content">
    @if($errors->any())
        <div class="container" style="margin-bottom:1rem">
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="container" style="margin-bottom:1rem">
            <div class="alert alert-error">{{ session('error') }}</div>
        </div>
    @endif
    @if(session('success'))
        <div class="container" style="margin-bottom:1rem">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif

    @yield('content')
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <a href="{{ route('home') }}" class="footer-logo">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:linear-gradient(135deg,#0d9488,#2dd4bf);border-radius:5px;color:#fff;font-weight:800;font-size:.7rem;">V</span>
                Vacanto
            </a>
            <p class="footer-tagline">Connecting talent with opportunity.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Platform</h4>
                <a href="{{ route('jobs.index') }}">Browse Jobs</a>
                <a href="{{ route('services.index') }}">Browse Services</a>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="background:none;border:none;padding:0;color:inherit;cursor:pointer;font:inherit">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    <a href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; {{ date('Y') }} Vacanto. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="{{ asset('assets/js/main.js') }}"></script>
@auth
<script>
(function() {
    setInterval(function() {
        fetch('{{ route('notifications.poll') }}')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var badge = document.getElementById('notifBadge');
                if (!badge) return;
                if (data.unread_total > 0) {
                    badge.textContent = data.unread_total;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }
            })
            .catch(function() {});
    }, 15000);
})();
</script>
@if(!auth()->user()->isAdmin())
<script>
(function() {
    if (window.location.pathname.indexOf('/messages/') !== -1 && window.location.pathname.indexOf('/messages/inbox') === -1) return;
    setInterval(function() {
        fetch('{{ route('messages.poll') }}?with=0&after=0')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var badge = document.getElementById('msgBadge');
                if (!badge) return;
                if (data.unread_total > 0) {
                    badge.textContent = data.unread_total;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }
            })
            .catch(function() {});
    }, 15000);
})();
</script>
@endif
@endauth
@stack('scripts')
</body>
</html>
