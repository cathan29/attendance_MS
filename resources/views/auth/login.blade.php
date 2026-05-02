<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>Login - Cipher Academy</title>
    <link rel="icon" type="image/png" href="{{ asset('images/school_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page" style="--auth-bg-image: url('{{ asset('images/school_background.png') }}');">
    <main class="auth-layout">
        <section class="auth-panel">
            <div class="auth-brand">
                <span class="brand-mark"><img src="{{ asset('images/school_logo.png') }}" alt="Cipher Academy logo"></span>
                <div>
                    <h1>Cipher Academy</h1>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="form-stack">
                @csrf
                <div class="auth-heading">
                    <h2>Sign in</h2>
                </div>
                <div class="field">
                    <label>Email or Employee ID</label>
                    <input class="form-control" name="employee_id" value="{{ old('employee_id') }}" placeholder="admin@cipheracademy.edu or ADMIN-001" required autofocus>
                </div>
                <div class="field">
                    <label>Password</label>
                    <div class="password-field">
                        <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" title="Show password">
                            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9.88 9.88A3 3 0 0 0 14.12 14.12"></path>
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c6.5 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                <path d="M6.61 6.61C3.63 8.63 2 12 2 12s3.5 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                <path d="M2 2l20 20"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <label class="check-row">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <button class="btn btn-primary btn-block">Log In</button>
            </form>
        </section>
    </main>
</body>
</html>
