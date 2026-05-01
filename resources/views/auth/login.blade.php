<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>Login - Attendance MS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-panel">
            <div class="auth-brand">
                <span class="brand-mark">A</span>
                <div>
                    <span class="eyebrow">Attendance platform</span>
                    <h1>Attendance MS</h1>
                    <p>Sign in to continue with a fast, focused attendance workspace.</p>
                </div>
            </div>

            <span class="auth-badge">Secure access</span>
            <div class="auth-copy">
                <p>Built for quick roll calls, clean records, and clear reporting without visual noise.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="form-stack">
                @csrf
                <div class="field">
                    <label>Employee ID</label>
                    <input class="form-control" name="employee_id" value="{{ old('employee_id') }}" placeholder="ADMIN-001" required autofocus>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                </div>
                <label class="check-row">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <button class="btn btn-primary btn-block">Log In</button>
            </form>
        </section>

        <section class="auth-showcase" aria-hidden="true">
            <div class="showcase-card">
                <span>Today</span>
                <strong>{{ now()->format('M d') }}</strong>
                <p>Fast, organized attendance for every class session.</p>
                <ul class="showcase-list">
                    <li>Quick roll call</li>
                    <li>Clear status badges</li>
                    <li>Export-ready records</li>
                </ul>
            </div>
        </section>
    </main>
</body>
</html>
