<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access denied</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="--school-bg-image: url('{{ asset('images/school_background.png') }}');">
<div class="app-shell" style="grid-template-columns: 1fr; min-height: 100vh;">
    <main class="main-content" style="margin: 0;">
        <section class="panel" style="max-width: 720px; margin: 80px auto;">
            <div class="section-title">
                <h1>Access denied</h1>
                <span class="chip-light">403</span>
            </div>

            <p class="text-muted">
                {{ $exception->getMessage() ?: 'You do not have permission to access this page.' }}
            </p>

            <div class="d-flex gap-2" style="margin-top: 20px;">
                @auth
                    <a class="btn btn-primary" href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('teacher.dashboard') }}">Go to dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" data-confirm="Are you sure you want to logout?">
                        @csrf
                        <button class="btn btn-outline-primary" type="submit">Logout</button>
                    </form>
                @else
                    <a class="btn btn-primary" href="{{ route('login') }}">Back to login</a>
                @endauth
            </div>
        </section>
    </main>
</div>
</body>
</html>
