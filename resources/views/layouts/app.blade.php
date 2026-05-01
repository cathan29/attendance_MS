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
    @php($role = auth()->user()->role)
    <aside class="sidebar">
        <a class="brand" href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('teacher.dashboard') }}">
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
        <header class="topbar">
            <div class="topbar-copy">
                <span class="eyebrow">{{ now()->format('l, F j') }}</span>
                <strong>{{ $role === 'admin' ? 'Administration' : 'Teacher Portal' }}</strong>
                <p>{{ $role === 'admin' ? 'Manage students, teachers, subjects, and attendance from one command center.' : 'Capture class attendance quickly with filters, clear status controls, and lightweight remarks.' }}</p>
            </div>
            <div class="topbar-meta">
                <span class="topbar-pill">{{ ucfirst($role) }} workspace</span>
                <span class="topbar-user">{{ auth()->user()->name }}</span>
            </div>
        </header>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
