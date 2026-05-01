<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>{{ $title ?? 'Cipher Academy' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/school_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell">
    @php($role = strtolower((string) auth()->user()->role))
    <aside class="sidebar">
        <a class="brand" href="{{ $role === 'admin' ? route('admin.dashboard') : route('teacher.dashboard') }}">
            <span class="brand-mark"><img src="{{ asset('images/school_logo.png') }}" alt="Cipher Academy logo"></span>
            <span>
                <strong>Cipher Academy</strong>
                <small>{{ ucfirst($role) }} workspace</small>
            </span>
        </a>

        <nav class="nav-list" aria-label="Primary navigation">
            @if($role === 'admin')
                <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span>Dashboard</span></a>
                <a class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}"><span>Students</span></a>
                <a class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}" href="{{ route('admin.teachers.index') }}"><span>Teachers</span></a>
                <a class="{{ request()->routeIs('admin.curriculum.*') ? 'active' : '' }}" href="{{ route('admin.curriculum.index') }}"><span>Curriculum</span></a>
                <a class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}" href="{{ route('admin.attendance.index') }}"><span>Attendance</span></a>
                <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><span>Reports</span></a>
            @else
                <a class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}"><span>Dashboard</span></a>
                <a class="{{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}" href="{{ route('teacher.attendance.create') }}"><span>Take Attendance</span></a>
            @endif
        </nav>

        <div class="sidebar-user account-card">
            <div class="account-main">
                <span class="avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}</span>
                <span class="user-copy">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->employee_id }}</small>
                </span>
            </div>
            <div class="account-actions">
                <span class="role-chip">{{ ucfirst($role) }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" title="Logout">Logout</button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main-content">

        @if(session('success') || $errors->any())
            <div class="toast-stack" aria-live="polite" aria-atomic="true">
                @if(session('success'))
                    <div class="toast-notice toast-success" role="status">
                        <span class="toast-dot"></span>
                        <div>
                            <strong>Success</strong>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button type="button" class="toast-close" aria-label="Close notification">x</button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="toast-notice toast-danger" role="alert">
                        <span class="toast-dot"></span>
                        <div>
                            <strong>Action needed</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                        <button type="button" class="toast-close" aria-label="Close notification">x</button>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    @if($role === 'teacher')
        <!-- Floating Menu Button -->
        <button class="floating-menu-btn" id="rightSidebarToggle" title="Toggle schedule sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
        </button>

        <aside class="right-sidebar" id="rightSidebar">
        <div class="sidebar-header">
            <h3>Schedule & Calendar</h3>
            <button class="sidebar-close" id="rightSidebarClose" title="Close sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="sidebar-content">
            <div class="weather-widget">
                <h4>Current Weather</h4>
                <div id="weather-container">
                    <div class="weather-placeholder">Loading...</div>
                </div>
            </div>
            <div class="calendar-widget">
                <div id="calendar-container" style="height: 300px; overflow-y: auto;">
                    <div class="calendar-placeholder">Loading calendar...</div>
                </div>
            </div>
            <div class="schedule-widget">
                <h4>Today's Schedule</h4>
                <div id="schedule-container">
                    <div class="schedule-placeholder">No schedule available</div>
                </div>
            </div>
            <div class="upcoming-widget">
                <h4>Upcoming Classes</h4>
                <div id="upcoming-container">
                    <div class="upcoming-placeholder">Loading classes...</div>
                </div>
            </div>
        </div>
        </aside>
    @endif
</div>

@if($role === 'teacher' && auth()->user()->must_update_credentials)
    <div class="modal-backdrop is-visible" data-required-modal>
        <section class="setup-modal" role="dialog" aria-modal="true" aria-labelledby="teacherSetupTitle">
            <div class="setup-modal-header">
                <span class="brand-mark"><img src="{{ asset('images/school_logo.png') }}" alt="Cipher Academy logo"></span>
                <div>
                    <span class="eyebrow">First login setup</span>
                    <h2 id="teacherSetupTitle">Update your account</h2>
                </div>
            </div>
            <p class="setup-copy">Please add your email and create a new password before continuing to your teacher workspace.</p>

            <form method="POST" action="{{ route('teacher.profile.credentials') }}" class="form-stack">
                @csrf
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', auth()->user()->email) }}" placeholder="teacher@cipheracademy.edu" required>
                </div>
                <div class="field">
                    <label>New Password</label>
                    <input type="password" class="form-control" name="password" placeholder="At least 8 characters with letters and numbers" required>
                </div>
                <div class="field">
                    <label>Confirm Password</label>
                    <input type="password" class="form-control" name="password_confirmation" placeholder="Repeat your new password" required>
                </div>
                <button class="btn btn-primary btn-block">Save and Continue</button>
            </form>
        </section>
    </div>
@endif
</body>
</html>
